package com.trw;

import org.apache.commons.lang.StringUtils;
import org.apache.poi.openxml4j.exceptions.InvalidFormatException;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.File;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.concurrent.atomic.AtomicInteger;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Created by tweissin on 11/22/16.
 */
public class TreeRouteCreator {
    public static final int MAX_ROUTES_PER_ZONE = 23;
    private GoogleRouteCollector googleRouteCollector = new GoogleRouteCollector();
    private static final Logger logger = LoggerFactory.getLogger(TreeRouteCreator.class);

    public static void main(String ... args) throws IOException, InvalidFormatException {
        new TreeRouteCreator().updateRoutes(1);
    }

    private void updateRoutes(int weekend) throws IOException, InvalidFormatException {
        File file = new File(Environment.ZONE_SPREADSHEET_FILE);

        // Make REST call to get all existing addresses
        Map<Integer, Map<String, String>> pickupInfos = RestUtils.getPickupInfo(Environment.DRIVER_USERNAME, Environment.DRIVER_PASSWORD, weekend);

        if (pickupInfos.size()==0) {
            logger.error("There are no pickups");
            return;
        }

        // Fix all addresses locally
        updatePickupInfos(pickupInfos, file);

        // Map all addresses to a zone
        updateZones(pickupInfos, file);

        // If street address doesn't have a zone, warn user and mark it so in DB
        // If any zone has more than 23, warn user

        // If all good, get optimal route and update address order with that route.
        addOptimalRoutes(pickupInfos);

        // Make REST call to update the following:
        //  - actual address
        //  - zone
        //  - route order

        logger.info("Done!!");
    }

    private void addOptimalRoutes(Map<Integer, Map<String, String>> pickupInfos) {
        logger.info("addOptimalRoutes");
        Map<String,List<Map<String, String>>> zoneToPickupInfos = new HashMap<>();
        pickupInfos.values().forEach(pickupInfo -> {
            String zone = pickupInfo.get("zone");
            List<Map<String, String>> pickupInfoList = zoneToPickupInfos.get(zone);
            if (pickupInfoList==null) {
                pickupInfoList = new ArrayList<>();
                zoneToPickupInfos.put(zone, pickupInfoList);
            }
            pickupInfoList.add(pickupInfo);
        });
        zoneToPickupInfos.forEach((zone,pickupInfoList) -> {
            logger.debug("addOptimalRoutes: zone " + zone);
            if (pickupInfoList.size()>MAX_ROUTES_PER_ZONE) {
                logger.error("too many pickups in zone " + zone + ": " + pickupInfoList.size());
                return;
//                throw new RuntimeException("too many pickups in zone " + zone + ": " + pickupInfoList.size());
            }
            List<String> addresses = new ArrayList<>();
            pickupInfoList.forEach(pickupInfo -> {
                addresses.add(pickupInfo.get("address"));
            });

            logger.debug("addOptimalRoutes: getOptimalRoute " + addresses);
            List<String> optimalRoute = googleRouteCollector.getOptimalRoute(Environment.STARTING_POINT, addresses, Environment.STARTING_POINT);

            for (int i=0; i<optimalRoute.size(); i++) {
                String address = optimalRoute.get(i);
                Map<String, String> pickupInfo = pickupInfoList.stream().filter(p -> p.get("address").equals(address)).findFirst().get();
                int id = Integer.valueOf(pickupInfo.get("id"));
                pickupInfo.put("route_order",String.valueOf(i));
                RestUtils.updatePickupInfo(Environment.DRIVER_USERNAME,Environment.DRIVER_PASSWORD,id,null,null, i);
            }
        });
    }

    private boolean updateZones(Map<Integer, Map<String, String>> pickupInfos, File file) throws IOException, InvalidFormatException {
        logger.info("updateZones - read from file " + file.getAbsolutePath());
        Map<String,String> roadZone = ZoneUtils.getRoadToZoneMap(file);
        Pattern pattern = Pattern.compile("\\d+ (.*?),.*");
        Pattern patternWithoutStreet = Pattern.compile("(.*?),.*");
        AtomicInteger changes = new AtomicInteger();

        pickupInfos.forEach((k,pickupInfo)->{
            String streetName;
            String actualAddress = pickupInfo.get("address");
            String currentZone = pickupInfo.get("zone");
            logger.debug("actualAddress: " + actualAddress);
            Matcher matcher = pattern.matcher(actualAddress);
            if (matcher.find()) {
                streetName = matcher.group(1);
            } else {
                matcher = patternWithoutStreet.matcher(actualAddress);
                if (matcher.find()) {
                    streetName = matcher.group(1);
                } else {
                    throw new RuntimeException("couldn't get street name from " + actualAddress);
                }
            }

            int poundPos = streetName.indexOf("#");
            if (poundPos!=-1) {
                streetName = streetName.substring(0, poundPos-1);
            }

            String zone = roadZone.get(streetName.toLowerCase());
            if (zone == null) {
                throw new RuntimeException("unknown zone for " + streetName + "; add it to the zone-mapping worksheet.");
            }

            if (!StringUtils.equals(currentZone,zone)) {
                pickupInfo.put("zone", zone);
                RestUtils.updatePickupInfo(Environment.DRIVER_USERNAME,Environment.DRIVER_PASSWORD,k,null,zone, -1);
                changes.incrementAndGet();
            }
        });
        return changes.get()>0;
    }

    private boolean updatePickupInfos(Map<Integer, Map<String, String>> pickupInfos, File file) throws IOException, InvalidFormatException {
        logger.info("updatePickupInfos");
        boolean useGoogleApi = false;
        AtomicInteger changes = new AtomicInteger();
        Map<String,String> addressExceptionMap = ZoneUtils.getAddressExceptionMap(file);
        pickupInfos.forEach((k,pickupInfo)->{
            String addressToLookup = pickupInfo.get("street");
            String fixedAddress = pickupInfo.get("address");
            logger.debug("addressToLookup: " + addressToLookup);
            if (addressExceptionMap.containsKey(addressToLookup.toLowerCase())) {
                addressToLookup = addressExceptionMap.get(addressToLookup.toLowerCase());
            }
            if (!addressToLookup.toLowerCase().contains(Environment.TOWN_LOWERCASE)) {
                addressToLookup += ", " + Environment.TOWN_AND_STATE;
            }

            String actualAddress;
            if (useGoogleApi || StringUtils.isEmpty(fixedAddress)) {
                actualAddress = googleRouteCollector.getAddress(addressToLookup);
                if (!actualAddress.equals(fixedAddress)) {
                    pickupInfo.put("address", actualAddress);
                    RestUtils.updatePickupInfo(Environment.DRIVER_USERNAME,Environment.DRIVER_PASSWORD,k,actualAddress,null, -1);
                    changes.incrementAndGet();
                }
            } else {
                actualAddress = fixedAddress;
            }
        });
        return changes.get()>0;
    }
}

package com.trw;

import com.google.maps.DirectionsApi;
import com.google.maps.GeoApiContext;
import com.google.maps.GeocodingApi;
import com.google.maps.model.DirectionsRoute;
import com.google.maps.model.GeocodingResult;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

/**
 * Created by tweissin on 11/22/16.
 */
public class GoogleRouteCollector {
    private GeoApiContext context;
    public static void main(String ... args) throws Exception {
        GoogleRouteCollector routeCollector = new GoogleRouteCollector();
        if (true) {
            String addr = routeCollector.getAddress("1000 Foo Rd Hopkinton MA");
            System.out.println(addr);
        } else {
            List<String> addresses = Arrays.asList("San Jose, CA","Miami, FL","Anchorage, AK","New York, NY");
            List<String> optimalAddresses = routeCollector.getOptimalRoute(
                    Environment.STARTING_POINT,
                    addresses,
                    Environment.STARTING_POINT);
            for (String address : optimalAddresses) {
                System.out.println(address);
            }
        }
    }

    public GoogleRouteCollector() {
        context = new GeoApiContext().setApiKey(Environment.GOOGLE_MAPS_API_KEY);
    }

    public List<String> getOptimalRoute(String startAddress, List<String> waypoints, String endAddress) {
        String[] strings = waypoints.toArray(new String[waypoints.size()]);
        DirectionsRoute[] routes;
        try {
            routes = DirectionsApi.newRequest(context)
                    .origin(startAddress)
                    .destination(endAddress)
                    .optimizeWaypoints(true)
                    .waypoints(strings)
                    .await();
        } catch (Exception e) {
            throw new RuntimeException("failed to get directions",e);
        }

        List<String> optimalRoute = new ArrayList<>(waypoints.size());
        for (int i=0; i<routes[0].waypointOrder.length; i++) {
            optimalRoute.add(waypoints.get(routes[0].waypointOrder[i]));
        }
        return optimalRoute;
    }

    public String getAddress(String address) {
        GeocodingResult[] results;
        try {
            results = GeocodingApi.geocode(context,
                    address).await();
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
        if (results.length!=1) {
            throw new RuntimeException("couldn't find address " + address + "; add to address-exception worksheet");
        }
        return results[0].formattedAddress;
    }
}

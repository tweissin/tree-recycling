package com.trw;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import org.apache.http.HttpHost;
import org.apache.http.HttpRequest;
import org.apache.http.HttpResponse;
import org.apache.http.auth.AuthScope;
import org.apache.http.auth.UsernamePasswordCredentials;
import org.apache.http.client.AuthCache;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.client.protocol.ClientContext;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.auth.BasicScheme;
import org.apache.http.impl.client.BasicAuthCache;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.protocol.BasicHttpContext;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * Created by tweissin on 11/22/16.
 */
public class RestUtils {
    private static final int PORT = 80;
    private static final Logger logger = LoggerFactory.getLogger(RestUtils.class);

    public static void main(String ... args) throws IOException {
        System.out.println(getPickupInfo(Environment.DRIVER_USERNAME, Environment.DRIVER_PASSWORD, 1));
    }

    /**
     * Returns a map of ID to pickup info.
     * It gets only the specified weekend.
     */
    public static Map<Integer,Map<String,String>> getPickupInfo(String username, String password, int weekendNum) throws IOException {
        logger.info("getPickupInfo");
        String json = getJson(username, password);
        List pickups = new Gson().fromJson(json, List.class);
        Map<Integer,Map<String,String>> addresses = new HashMap<>();
        for (Object pickup : pickups) {
            if (pickup instanceof Map) {
                String weekend = (String) ((Map)pickup).get("weekend");
                String compareWeeknd = "date_" + (weekendNum-1);
                if (compareWeeknd.equals(weekend)) {
                    Map<String,String> pickupInfo = new HashMap<>();
                    String id = (String) ((Map)pickup).get("id");
                    pickupInfo.put("id",id);
                    pickupInfo.put("street",(String) ((Map)pickup).get("street"));
                    pickupInfo.put("address",(String) ((Map)pickup).get("address"));
                    pickupInfo.put("weekend",(String) ((Map)pickup).get("weekend"));
                    pickupInfo.put("zone",(String) ((Map)pickup).get("zone"));
                    addresses.put(Integer.valueOf(id), pickupInfo);
                }
            }
        }
        return addresses;
    }

    public static void updatePickupInfo(String username, String password, int id,
                                        String address,
                                        String zone,
                                        Integer route_order) {
        Map<String, String> myMap = new HashMap<>();
        myMap.put("id", String.valueOf(id));

        if (address!=null) myMap.put("address", address);
        if (zone!=null) myMap.put("zone", zone);
        if (route_order!=null) myMap.put("route_order", route_order.toString());

        Gson gson = new GsonBuilder().create();
        String json = gson.toJson(myMap);
        try {
            putJson(username,password,json);
        } catch (IOException e) {
            throw new RuntimeException("failed to update pickup info",e);
        }
    }

    public static Map<String,String> getSpreadsheetAsMap(String spreadsheetId, String sheetId) throws IOException {
        Map<String,String> map = new HashMap<>();
        Map<String,String> dataMap = getSpreadsheetDataMap(spreadsheetId, sheetId);
        int currentRow = 2;
        while(true) {
            String key = dataMap.get("A" + currentRow);
            if (key==null) {
                break;
            }
            String value = dataMap.get("B" + currentRow);
            map.put(key,value);
            currentRow++;
        }
        return map;
    }

    /**
     * This returns the spreadsheet data in the form {"A1":"data1", "A2":"data2", "B1":"data3", ...}
     */
    private static Map<String,String> getSpreadsheetDataMap(String spreadsheetId, String sheetId) throws IOException {
        Map<String,String> dataMap = new HashMap<>();
        String json = getJsonForSpreadsheet(spreadsheetId, sheetId);
        Map spreadsheet = new Gson().fromJson(json, Map.class);
        Map feed = (Map)spreadsheet.get("feed");
        List entries = (List)feed.get("entry");
        for (Object entry : entries) {
            Map entryMap = (Map)entry;
            Map titleMap = (Map) entryMap.get("title");
            String cell = (String) titleMap.get("$t");
            Map cellMap = (Map) entryMap.get("gs$cell");
            String data = (String) cellMap.get("inputValue");
            dataMap.put(cell,data);
        }
        return dataMap;
    }

    private static String getJsonForSpreadsheet(String spreadsheetId, String sheetId) throws IOException {
        HttpHost targetHost = new HttpHost("spreadsheets.google.com", PORT, "http");
        DefaultHttpClient httpclient = new DefaultHttpClient();
        HttpGet httpGet = new HttpGet("/feeds/cells/" + spreadsheetId + "/" + sheetId + "/public/full?alt=json");
        HttpResponse response = httpclient.execute(targetHost, httpGet);
        return responseAsString(response);
    }

    private static String responseAsString(HttpResponse response) throws IOException {
        try (BufferedReader in = new BufferedReader(new InputStreamReader(response.getEntity().getContent()))) {
            StringBuilder sb = new StringBuilder("");
            String line;
            String NL = System.getProperty("line.separator");
            while ((line = in.readLine()) != null) {
                sb.append(line).append(NL);
            }
            return sb.toString();
        }
    }

    private static HttpResponse getHttpResponse(String username, String password, HttpRequest httpRequest) throws IOException {
        HttpHost targetHost = new HttpHost(Environment.HOST, PORT, "http");

        DefaultHttpClient httpclient = new DefaultHttpClient();
        httpclient.getCredentialsProvider().setCredentials(
                new AuthScope(targetHost.getHostName(), targetHost.getPort()),
                new UsernamePasswordCredentials(username, password));

        // Create AuthCache instance
        AuthCache authCache = new BasicAuthCache();
        // Generate BASIC scheme object and add it to the local auth cache
        BasicScheme basicAuth = new BasicScheme();
        authCache.put(targetHost, basicAuth);

        // Add AuthCache to the execution context
        BasicHttpContext localcontext = new BasicHttpContext();
        localcontext.setAttribute(ClientContext.AUTH_CACHE, authCache);

        return httpclient.execute(targetHost, httpRequest, localcontext);
    }

    private static String getJson(String username, String password) throws IOException {
        HttpGet httpget = new HttpGet("/driver/php/db-get-pickups.php");
        HttpResponse response = getHttpResponse(username, password, httpget);
        return responseAsString(response);
    }

    private static void putJson(String username, String password, String json) throws IOException {
        HttpPost httpPost = new HttpPost("/driver/php/db-update-pickup-info.php");
        httpPost.setEntity(new StringEntity(json));
        HttpResponse response = getHttpResponse(username, password, httpPost);
        if (response.getStatusLine().getStatusCode()!=200) {
            throw new RuntimeException("server returned status code " + response.getStatusLine().getStatusCode());
        }
    }
}

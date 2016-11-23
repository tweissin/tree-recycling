package com.trw;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import org.apache.http.HttpHost;
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
     */
    public static Map<Integer,Map<String,String>> getPickupInfo(String username, String password, int weekendNum) throws IOException {
        logger.info("getPickupInfo");
        String json = getJson(username, password);
        List pickups = new Gson().fromJson(json, List.class);
        Map<Integer,Map<String,String>> addresses = new HashMap<>();
        for (Object pickup : pickups) {
            if (pickup instanceof Map) {
                String weekend = (String) ((Map)pickup).get("weekend");
                if (String.valueOf(weekendNum).equals(weekend)) {
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

    private static String getJson(String username, String password) throws IOException {
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

        HttpGet httpget = new HttpGet("/driver/php/db-get-pickups.php");
        HttpResponse response = httpclient.execute(targetHost, httpget, localcontext);
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

    private static void putJson(String username, String password, String json) throws IOException {
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

        HttpPost httpPost = new HttpPost("/driver/php/db-update-pickup-info.php");
        httpPost.setEntity(new StringEntity(json));
        HttpResponse response = httpclient.execute(targetHost, httpPost, localcontext);
        if (response.getStatusLine().getStatusCode()!=200) {
            throw new RuntimeException("server returned status code " + response.getStatusLine().getStatusCode());
        }
    }
}

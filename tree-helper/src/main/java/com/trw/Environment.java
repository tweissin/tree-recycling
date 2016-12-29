package com.trw;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.util.Properties;

/**
 * Created by tweissin on 11/22/16.
 */
public class Environment {
    public static final String GOOGLE_MAPS_API_KEY;
    public static final String DRIVER_USERNAME;
    public static final String DRIVER_PASSWORD;
    public static final String HOST;
    public static final String STARTING_POINT;
    public static final String TOWN_LOWERCASE;
    public static final String TOWN_AND_STATE;
    public static final String ZONE_SPREADSHEET_FILE;
    public static File propertiesFilename = new File(System.getProperty("user.dir") + "/tree-helper.properties");

    static {
        Properties props = getProperties();
        GOOGLE_MAPS_API_KEY = props.getProperty("google.maps.api.key");
        DRIVER_USERNAME = props.getProperty("driver.username");
        DRIVER_PASSWORD = props.getProperty("driver.password");
        STARTING_POINT = props.getProperty("starting.point");
        TOWN_LOWERCASE = props.getProperty("town.lowercase");
        TOWN_AND_STATE = props.getProperty("town.and.state");
        ZONE_SPREADSHEET_FILE = props.getProperty("zone.spreadsheet.file");
        HOST = props.getProperty("host");
    }

    private static Properties getProperties() {
        Properties props = new Properties();
        try (InputStream is=new FileInputStream(propertiesFilename)) {
                props.load(is);
            } catch (IOException e) {
                throw new RuntimeException("problem loading from file", e);
            }
        return props;
    }
}

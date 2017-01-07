package com.trw;

import org.apache.poi.openxml4j.exceptions.InvalidFormatException;

import java.io.IOException;
import java.util.List;
import java.util.Map;

/**
 * Created by tweissin on 12/30/16.
 */
public class ZoneUtils {
    private static ZoneUtils INSTANCE;

    public static ZoneUtils getInstance() {
        if (INSTANCE==null) {
            INSTANCE = new ZoneUtils();
        }
        return INSTANCE;
    }

    Map<String, List<RestUtils.ZoneAndRange>> getRoadToZoneMap() throws IOException, InvalidFormatException {
        Map<String,List<RestUtils.ZoneAndRange>> map = RestUtils.getZoneMapping(Environment.ZONE_SPREADSHEET_ID, "1");
        return map;
    }

    Map<String, String> getAddressExceptionMap() throws IOException, InvalidFormatException {
        Map<String, String> map = RestUtils.getSpreadsheetAsMap(Environment.ZONE_SPREADSHEET_ID, "3");
        return map;
    }

    Map<String, String> getMetaZoneMap() throws IOException, InvalidFormatException {
        Map<String, String> map = RestUtils.getSpreadsheetAsMap(Environment.ZONE_SPREADSHEET_ID, "2");
        return map;
    }
}

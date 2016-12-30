package com.trw;

import org.apache.poi.openxml4j.exceptions.InvalidFormatException;

import java.io.File;
import java.io.IOException;
import java.util.Map;

/**
 * This gets the zone mapping from a Google Sheets spreadsheet.
 */
public class GoogleSheetsZoneUtils extends ZoneUtils {
    public static void main(String [] args) throws IOException, InvalidFormatException {
        ZoneUtils.getInstance().getRoadToZoneMap(null);
    }

    @Override
    Map<String, String> getRoadToZoneMap(File xlsxFile) throws IOException, InvalidFormatException {
        Map<String, String> map = RestUtils.getSpreadsheetAsMap(Environment.ZONE_SPREADSHEET_ID, "1");
        return map;
    }

    @Override
    Map<String, String> getMetaZoneMap(File xlsxFile) throws IOException, InvalidFormatException {
        Map<String, String> map = RestUtils.getSpreadsheetAsMap(Environment.ZONE_SPREADSHEET_ID, "2");
        return map;
    }

    @Override
    Map<String, String> getAddressExceptionMap(File xlsxFile) throws IOException, InvalidFormatException {
        Map<String, String> map = RestUtils.getSpreadsheetAsMap(Environment.ZONE_SPREADSHEET_ID, "3");
        return map;
    }
}

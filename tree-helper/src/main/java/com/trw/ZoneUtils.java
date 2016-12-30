package com.trw;

import org.apache.poi.openxml4j.exceptions.InvalidFormatException;

import java.io.File;
import java.io.IOException;
import java.util.Map;

/**
 * Created by tweissin on 12/30/16.
 */
public abstract class ZoneUtils {
    private static ZoneUtils INSTANCE;

    public static ZoneUtils getInstance() {
        if (INSTANCE==null) {
            INSTANCE = new GoogleSheetsZoneUtils();
        }
        return INSTANCE;
    }

    abstract Map<String, String> getRoadToZoneMap(File xlsxFile) throws IOException, InvalidFormatException;

    abstract Map<String, String> getAddressExceptionMap(File xlsxFile) throws IOException, InvalidFormatException;

    abstract Map<String, String> getMetaZoneMap(File xlsxFile) throws IOException, InvalidFormatException;
}

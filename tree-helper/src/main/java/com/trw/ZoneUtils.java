package com.trw;

import org.apache.poi.openxml4j.exceptions.InvalidFormatException;
import org.apache.poi.ss.usermodel.*;

import java.io.*;
import java.util.HashMap;
import java.util.Map;

/**
 * Created by tweissin on 11/22/16.
 */
public class ZoneUtils {
    public static void main(String [] args) throws IOException, InvalidFormatException {
        ZoneUtils helper = new ZoneUtils();
        File file = new File(Environment.ZONE_SPREADSHEET_FILE);
        Map<String, String> roadToZoneMap = getRoadToZoneMap(file);
        Map<String, String> map = loadMapFromSheet(file, "zone-mapping");
        helper.dumpZones("/street-names.txt", roadToZoneMap);

        Map<String, String> zoneMetaMap = getMetaZoneMap(file);
        System.out.println(zoneMetaMap);
    }

    private void dumpZones(String filename, Map<String, String> roadToZoneMap) throws IOException {
        InputStream is = ZoneUtils.class.getResourceAsStream(filename);
        BufferedReader reader = new BufferedReader(new InputStreamReader(is));
        String road;
        while((road=reader.readLine())!=null) {
            String zone = roadToZoneMap.get(road.trim());
            if(zone==null) {
                System.out.println("no mapping for " + road);
                continue;
            }
            System.out.println(zone);
        }
    }

    /**
     * Used to map an address to a zone.
     */
    public static Map<String, String> getRoadToZoneMap(File xlsxFile) throws IOException, InvalidFormatException {
        return loadMapFromSheet(xlsxFile, "zone-mapping");
    }

    /**
     * Returns meta information about a zone.
     */
    public static Map<String, String> getMetaZoneMap(File xlsxFile) throws IOException, InvalidFormatException {
        return loadMapFromSheet(xlsxFile, "zone-meta");
    }

    /**
     * Returns map for exceptional cases when the street name is not known.
     */
    public static Map<String, String> getAddressExceptionMap(File xlsxFile) throws IOException, InvalidFormatException {
        return loadMapFromSheet(xlsxFile, "address-exception");
    }

    private static Map<String,String> loadMapFromSheet(File xlsxFile, String sheetName) throws IOException, InvalidFormatException {
        Map<String,String> sheetMap = new HashMap<>();

        try (FileInputStream fis = new FileInputStream(xlsxFile)) {
            Workbook wb = WorkbookFactory.create(fis);
            Sheet sheet1 = wb.getSheet(sheetName);
            for (Row row : sheet1) {
                if (sheet1.getFirstRowNum()==row.getRowNum()) {
                    // skip header
                    continue;
                }
                Cell keyCell = row.getCell(0);
                Cell valueCell = row.getCell(1);

                String key;
                String value;
                if (keyCell.getCellType()==Cell.CELL_TYPE_NUMERIC) {
                    key = String.valueOf((int)keyCell.getNumericCellValue());
                } else {
                    key = keyCell.getStringCellValue();
                }
                if (valueCell.getCellType()==Cell.CELL_TYPE_NUMERIC) {
                    value = String.valueOf((int)valueCell.getNumericCellValue());
                } else {
                    value = valueCell.getStringCellValue();
                }

                sheetMap.put(key.toLowerCase(), value.toLowerCase());
            }
        }
        return sheetMap;
    }
}

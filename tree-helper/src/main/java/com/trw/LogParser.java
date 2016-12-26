package com.trw;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import java.util.stream.Stream;

/**
 * This parses the a log file and outputs it to pipe-delimited.
 */
public class LogParser {
    public static void main(String ... args) throws IOException {
        new LogParser().run();
    }

    private void run() throws IOException {
        Pattern pattern = Pattern.compile("\\[(.*?)\\]");
        try (Stream<String> stream = Files.lines(Paths.get("/Users/tweissin/Downloads/log-2015-2016.txt"))) {

            stream.forEach(line -> {
                if (!(line.contains("New Request") && !line.contains("success"))) {
                    return;
                }
                Matcher m = pattern.matcher(line);
                if (m.find()) {
                    String date = m.group(1);
                    String [] dateparts = date.split(" ");
                    System.out.println(dateparts[0] + "|" + dateparts[1] + "|" + line.substring(35));
                }
            });

        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}

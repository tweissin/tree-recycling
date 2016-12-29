package com.trw;

import ch.qos.logback.classic.spi.ILoggingEvent;
import org.apache.poi.openxml4j.exceptions.InvalidFormatException;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.swing.*;
import java.awt.*;
import java.awt.event.KeyEvent;
import java.awt.event.WindowAdapter;
import java.awt.event.WindowEvent;
import java.io.File;
import java.io.IOException;
import java.util.concurrent.Semaphore;

public class TreeRouteCreatorUI extends JDialog {
    private JPanel contentPane;
    private JButton buttonExit;
    private JTextArea textAreaLog;
    private JButton buttonStart;
    private JRadioButton weekend1RadioButton;
    private JRadioButton weekend2RadioButton;
    private JTextField textFieldPropertiesFile;
    private JButton buttonPickFile;
    private static final Logger logger = LoggerFactory.getLogger(TreeRouteCreator.class);
    private Semaphore semaphore = new Semaphore(1);

    public TreeRouteCreatorUI() {
        setContentPane(contentPane);
        setModal(true);
        getRootPane().setDefaultButton(buttonExit);

        buttonExit.addActionListener(e -> onOK());

        buttonStart.addActionListener(e -> onStart());

        // call onCancel() when cross is clicked
        setDefaultCloseOperation(DO_NOTHING_ON_CLOSE);
        addWindowListener(new WindowAdapter() {
            public void windowClosing(WindowEvent e) {
                onCancel();
            }
        });

        // call onCancel() on ESCAPE
        contentPane.registerKeyboardAction(e -> onCancel(), KeyStroke.getKeyStroke(KeyEvent.VK_ESCAPE, 0), JComponent.WHEN_ANCESTOR_OF_FOCUSED_COMPONENT);

        UIAppender.getInstance().setEvaluator((ILoggingEvent e) -> {
            SwingUtilities.invokeLater(() -> textAreaLog.append(e.toString() + "\n"));
            return true;
        });
        buttonPickFile.addActionListener(e -> onPickFile());
    }

    private void onPickFile() {
        final JFileChooser fileChooser = new JFileChooser(System.getProperty("user.dir"));
        int returnVal = fileChooser.showOpenDialog(this);
        if (returnVal==JFileChooser.APPROVE_OPTION) {
            File file = fileChooser.getSelectedFile();
            textFieldPropertiesFile.setText(file.getAbsolutePath());
        }
    }

    private void onStart() {
        int weekend = -1;
        if (weekend1RadioButton.isSelected()) {
            weekend = 1;
        } else if (weekend2RadioButton.isSelected()) {
            weekend = 2;
        }
        if (weekend==-1) {
            logger.warn("No weekend selected");
            JOptionPane.showMessageDialog(this, "Select a weekend");
            return;
        }
        if (textFieldPropertiesFile.getText().length()==0) {
            logger.warn("No properties file selected");
            JOptionPane.showMessageDialog(this, "Must specify properties filename");
            return;
        }
        File propertiesFile = new File(textFieldPropertiesFile.getText());
        if (!propertiesFile.exists()) {
            logger.warn("Properties file does not exist: " + textFieldPropertiesFile.getText());
            JOptionPane.showMessageDialog(this, "File does not exist: " + textFieldPropertiesFile.getText());
            return;
        }
        Environment.propertiesFilename = propertiesFile;
        if (!semaphore.tryAcquire()) {
            logger.warn("Must wait for current process to complete");
            return;
        }
        final Component parent = this;
        new Thread(() -> {
            try {
                new TreeRouteCreator().updateRoutes(1);
            } catch (IOException | InvalidFormatException e) {
                JOptionPane.showMessageDialog(parent, "Problem updating routes (check log): " + e.getMessage());
            } finally {
                semaphore.release();
            }
        }).start();
    }

    private void onOK() {
        // add your code here
        dispose();
    }

    private void onCancel() {
        // add your code here if necessary
        dispose();
    }

    public static void main(String[] args) {
        TreeRouteCreatorUI dialog = new TreeRouteCreatorUI();
        dialog.pack();
        dialog.setVisible(true);
        System.exit(0);
    }
}

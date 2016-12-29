package com.trw;

import ch.qos.logback.classic.boolex.IEvaluator;
import ch.qos.logback.classic.spi.ILoggingEvent;
import ch.qos.logback.core.AppenderBase;

/**
 * Created by tweissin on 12/28/16.
 */
public class UIAppender extends AppenderBase<ILoggingEvent> {
    private static UIAppender INSTANCE = null;
    private IEvaluator evaluator;

    public UIAppender() {
        INSTANCE = this;
    }
    public static UIAppender getInstance() {
        return INSTANCE;
    }

    @Override
    protected void append(ILoggingEvent o) {
        this.evaluator.doEvaluate(o);
    }

    public void setEvaluator(IEvaluator evaluator) {
        this.evaluator = evaluator;
    }
}

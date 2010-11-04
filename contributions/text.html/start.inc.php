<?php
// Register our view event handler
EventSource::Instance()->register(new TextHtmlEventSink());


/**
 * @group Html
 * @ingroup Text
 * @author Gerd Riesselmann
 * 
 * This module centralizes functionality related to html processing.
 */

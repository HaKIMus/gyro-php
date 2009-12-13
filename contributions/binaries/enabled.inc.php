<?php
/**
 * @defgroup Binaries
 * 
 * Keeps files in the database and manages file uploads 
 * 
 * The binaries module introduces a special template that can hold uploaded files 
 * and make tham accessible to the webbrowser at /binaries/view/{id}.
 * 
 * The module and namely the view action takes care of the uploaded file's 
 * mime type. It depends on the mime module to handle output.
 * 
 * Uploading a file must be handled by the using application itself. The 
 * gyro core offers an upload widget that can be used to print the input widget:
 * 
 * @code
 * print WidgetInput::output(
 *  'fileup', 
 *  'Choose a file', 
 *  '',  // File input does not accept a value!
 *  WidgetInput::FILE, 
 *  array('accept' => 'image/jpeg')
 * );
 * @endcode
 * 
 * @attention
 *   Note that the enclosing form must have the attribute enctype="multipart/form-data". 
 * 
 * The Gyro core internally transforms the $_FILE and associated arrays to
 * be included in the PageData post-array. We therefor can write the 
 * following code:
 * 
 * 
 * @code
 * $fileup = $page_data->get_post()->get_item('fileup');
 * if (Binaries::is_upload($fileup)) {
 *   $binary = false;
 *   $err = Binaries::create_from_post($fileup, $binary);
 *   if ($err->is_ok()) {
 *     // Binary was created, we can now link to it, e.g.
 *   }
 * }
 * @endcode
 * 
 * This will work, even if the user did not upload anything.
 * 
 * If an upload is required, just let create_from_post() do the work:
 * 
 * @code
 * $fileup = $page_data->get_post()->get_item('fileup');
 * $binary = false;
 * $err = Binaries::create_from_post($fileup, $binary);
 * if ($err->is_ok()) {
 *   // Binary was created, we can now link to it, e.g.
 * }
 * @endcode
 * 
 * Note that the application should take care of deleting old associations, 
 * if a new file was uploaded (this will become a new instance with a new id!).
 */

Load::enable_module('mime');

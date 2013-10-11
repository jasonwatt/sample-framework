<?PHP
/**
 * Utility function to be used in the app
 * Author: Jason Watt
 */
require(LIB . "exception.php");
require(VENDOR . DS . "email/email.php");

function send_email($to, $subject, $body, $from = "admin@doriansparlor.com") {
  $mail = new PHPMailer();

  $mail->From     = 'contact@doriansparlor.com';
  $mail->FromName = 'Dorian\'s Contact';
  $mail->AddAddress($to);
  $mail->AddReplyTo($from, $from);
  $mail->Subject  = $subject;
  $mail->Body     = $body;
  $mail->WordWrap = 100;
  $mail->IsHTML(true);
  $mail->AltBody = html2txt($body);

  if (!$mail->Send()) {
    if (RUN_ON_DEVELOPMENT) {
      echo $mail->ErrorInfo; //spit that bug out :P
    }

    return false;
  } else {
    return true;
  }
}

/**
 * Converts HTML to readable text
 * @param $document
 *
 * @return string
 */
function html2txt($document) {
  $search  = array("'<script[^>]*?>.*?</script>'si", // strip out javascript
                   "'<[\/\!]*?[^<>]*?>'si", // strip out html tags
                   "'([\r\n])[\s]+'", // strip out white space
                   "'@<![\s\S]*?–[ \t\n\r]*>@'",
                   "'&(quot|#34|#034|#x22);'i", // replace html entities
                   "'&(amp|#38|#038|#x26);'i", // added hexadecimal values
                   "'&(lt|#60|#060|#x3c);'i",
                   "'&(gt|#62|#062|#x3e);'i",
                   "'&(nbsp|#160|#xa0);'i",
                   "'&(iexcl|#161);'i",
                   "'&(cent|#162);'i",
                   "'&(pound|#163);'i",
                   "'&(copy|#169);'i",
                   "'&(reg|#174);'i",
                   "'&(deg|#176);'i",
                   "'&(#39|#039|#x27);'",
                   "'&(euro|#8364);'i", // europe
                   "'&a(uml|UML);'", // german
                   "'&o(uml|UML);'",
                   "'&u(uml|UML);'",
                   "'&A(uml|UML);'",
                   "'&O(uml|UML);'",
                   "'&U(uml|UML);'",
                   "'&szlig;'i",
  );
  $replace = array("",
                   "",
                   " ",
                   "\"",
                   "&",
                   "<",
                   ">",
                   " ",
                   chr(161),
                   chr(162),
                   chr(163),
                   chr(169),
                   chr(174),
                   chr(176),
                   chr(39),
                   chr(128),
                   "ä",
                   "ö",
                   "ü",
                   "Ä",
                   "Ö",
                   "Ü",
                   "ß",
  );

  $text = preg_replace($search, $replace, $document);

  return trim($text);
}

/**
 * Debug function allowing quick stack traces for development and exceptions
 */
function dbug() {
  if (!isset($doc_root)) {
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
  }
  $back = debug_backtrace();
  // you may want not to htmlspecialchars here
  $line = htmlspecialchars($back[0]['line']);
  $file = htmlspecialchars(str_replace(array('\\', $doc_root), array('/', ''), $back[0]['file']));

  $k = (isset($back[1]) && $back[1]['class'] == 'SQL') ? 3 : 1;

  $class    = !empty($back[$k]['class']) ? htmlspecialchars($back[$k]['class']) . '::' : '';
  $function = !empty($back[$k]['function']) ? htmlspecialchars($back[$k]['function']) . '() ' : '';
  $args     = (count($back[0]['args'] > 0)) ? $back[0]['args'] : $back[0]['object'];
  $args     = (count($args) == 1 && $args[0] != '') ? $args[0] : $args;

  print '<div style="background-color:#eee; width:100%; font-size:11px; font-family: Courier, monospace;" class="myDebug"><div style=" font-size:12px; padding:3px; background-color:#ccc">';
  print "<b>$class$function =&gt;$file #$line</b></div>";
  print '<div style="padding:5px;">';
  if (is_array($args) || is_object($args)) {
    print '<pre>';
    print_r($args);
    print '</pre></div>';
  } else {
    print $args . '</div>';
  }
  print '</div>';
}
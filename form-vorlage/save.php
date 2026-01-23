<?php declare(strict_types=1);
/**
 * Vorlage: Kopieren und anpassen
 * Projectname: ...
 */

/**
 * change this to success script
 */
$redirect_on_success = 'success.php';
/**
 * change this to error script
 */
$redirect_on_error = 'fehler.php';

require_once '../config.php';
require_once '../include/utils.php';

/**
 * Fields speichern
 * 
 * Bei Erfolg: Weiterleitung an $redirect_on_success
 * Bei FehlerL Weiterleitung an $redirect_on_error
 */
if( check_parameter( $POST, [ 'field1', 'field2' ] ) ) {
  try {
    if( $success ) {
      header( 'Location: ' . $redirect_on_success );
      exit( 0 );
    }
  } catch ( Exception $e ) {
    header( 'Location: ' . $redirect_on_error . '?error=' . urlencode( $e->getMessage() ) );
    exit( 0 );
  }
}

header( 'Location: ' . $redirect_on_error . '?error=' . urlencode( 'parameter missing') );
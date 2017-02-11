<?php
/**
 * SysInfo Plugin: Displays information about host server
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Satoshi Sahara <sahara.satoshi@gmail.com>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_adminhomepage extends DokuWiki_Syntax_Plugin {

    protected $mode;

    function getType() {  return 'substition'; }
    function getPType() { return 'normal'; }
    function getSort() {  return 155; }

    function __construct() {
        $this->mode = substr(get_class($this), 7); // drop 'syntax_' from class name
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('%SYSINFO:\w+%', $mode, $this->mode);
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        $match = substr($match,9,-1); //strip %SYSINFO: from start and % from end
        return array(strtolower($match));
    }

    /**
     * Create output
     */
    function render($format, Doku_Renderer $renderer, $data) {
        if ($format == 'xhtml'){

            //handle various info stuff
            switch ($data[0]){
                case 'php_version':
                    $out = phpversion();
                    break;
                case 'gd_version':
                    if (extension_loaded('gd')) {
                        $gdinfo = gd_info();
                        $out = $gdinfo['GD Version'];
                    } else {
                        $out = 'not supported';
                    }
                    break;
                case 'mb_internal_encoding':
                    if (extension_loaded('mbstring')) {
                        $out = mb_get_info('internal_encoding');
                    } else {
                        $out = '⯑not supported';
                    }
                    break;
                case 'os':
                    $out = php_uname('s').' '.php_uname('r');
                    break;
                default:
                    $out = '⯑no info about '.htmlspecialchars($data[0]);
            }
            $renderer->doc .= $out;
            return true;
        }
        return false;
    }

}

<?
/**
 * 문법 플러그인 클래스
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */

class NarinSyntaxPlugin extends NarinPlugin {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}	
	
	public function getType() {
		return "syntax";
	}

	/**
	 * 하위 클래스에서 중복구현해야 함
	 */
	public function register($parser) {}
}
?>
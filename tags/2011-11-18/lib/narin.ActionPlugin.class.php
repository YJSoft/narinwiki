<?
/**
 * 액션 플러그인 클래스
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     byfun (http://byfun.com)
 */
 
class NarinActionPlugin extends NarinPlugin {
		
	/**
	 * 생성자
	 */
	public function __construct() {
		parent::__construct();
	}		
	
	/**
	 * 하위 클래스에서 중복구현해야 함
	 */
	public function register($ctrl) {}
		
	/**
	 * 플러그인 유형
	 */
	public function getType() {
		return "action";
	}
}
?>
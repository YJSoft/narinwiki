<?
/**
 *
 * 문법 플러그인 클래스
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

class NarinSyntaxPlugin extends NarinPlugin {

	/**
	 * 생성자
	 */
	public function __construct() {
		parent::__construct();
	}

	public function getType() {
		return "syntax";
	}

	/**
	 *
	 * 하위 클래스에서 중복구현해야 함
	 *
	 * @param NarinParser $parser 파서 인스턴스
	 */
	public function register($parser) {}
}
?>
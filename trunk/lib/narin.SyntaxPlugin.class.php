<?
/**
 *
 * 문법 플러그인 클래스 스크립트
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 문법 플러그인 클래스.
 * 문법 플러그인은 이 클래스를 상속받아 구현해야 한다.
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */

class NarinSyntaxPlugin extends NarinPlugin {

	/**
	 * 
	 * @var int 파서 등록 순서 (작은 번호 일 수 록 파서에 먼저 등록됨)
	 */
	var $order = 5555;
	
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
	
	/**
	 * 변수 초기화를 위해 사용
	 */
	public function init() {}
	
}
?>

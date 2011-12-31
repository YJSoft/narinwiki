<?
/**
 *
 * 나린위키 액션 플러그인 클래스 스크립트
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 액션 플러그인 클래스
 *
 * 액션 플러그인은 이 클래스를 상속받아 구현해야 한다.
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinActionPlugin extends NarinPlugin {

	/**
	 * 생성자
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 *
	 * 이벤트 핸들러 등록
	 * 
	 * 하위 클래스에서 중복구현해야 함
	 * 
	 * @param NarinEvent $ctrl {@link NarinEvent} 클래스
	 */
	public function register($ctrl) {}

	/**
	 *
	 * 플러그인 유형 반환
	 * 
	 * @return string "action"
	 */
	public function getType() {
		return "action";
	}
}
?>

<?
/**
 *
 * 나린위키 관리(admin) 플러그인 클래스 스크립트
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */

/**
 *
 * 나린위키 관리(admin) 플러그인 클래스
 *
 * 관리 플러그인은 이 클래스를 상속받아 구현해야 한다.
 *
 * @package	narinwiki
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 */
class NarinAdminPlugin extends NarinPlugin {

	/**
	 * 생성자
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 *
	 * 관리자 페이지 목록에 보여질 플러그인 이름
	 * 
	 * 하위 클래스에서 중복구현해야 함
	 * 
	 * @return string 플러그인 이름
	 */
	public function getName() {}
	
	/**
	 *
	 * 관리자 페이지 목록에 보여질 플러그인 설명
	 * 
	 * 하위 클래스에서 중복구현해야 함
	 * 
	 * @return string 플러그인 설명
	 */
	public function getDescription() {}	
	
	/**
	 *
	 * 관리자 페이지 목록에 출력될 순서 (기본값 1000)
	 * 
	 * 하위 클래스에서 중복구현해야 함	 
	 * 
	 * @return int 정렬 순서
	 */
	public function getOrder() { return 1000;}		
	
	/**
	 *
	 * 플러그인 메인 페이지 출력
	 * 
	 * @param array $params adm/admin.plugin.php 에서 전달하는 파라미터
	 */	
	public function view($params) { echo "구현되지 않았습니다."; }

	/**
	 *
	 * 플러그인 유형 반환
	 * 
	 * @return string "action"
	 */
	public function getType() {
		return "admin";
	}
	
}
?>

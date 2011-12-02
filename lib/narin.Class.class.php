<?
/**
 *
 * 나린위키 최상위 클래스 스크립트
 *
 * 최상위 클래스로 모든 나린위키 클래스가 이 클래스를 상속받는다.
 * 클래스들에서 주로쓰는 맴버 변수들을 정의한다.
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 * @filesource
 */

if (!defined('_GNUBOARD_')) exit;

/**
 *
 * 나린위키 최상위 클래스
 *
 * 최상위 클래스로 모든 나린위키 클래스가 이 클래스를 상속받는다.
 * 클래스들에서 주로쓰는 맴버 변수들을 정의한다.
 *
 * @package	narinwiki
 * @license http://narin.byfun.com/license GPL2
 * @author	byfun (http://byfun.com)
 */
class NarinClass {
	
	/**
	 * 
	 * @var NarinConfig 나린위키 환경설정 클래스
	 */
	protected $wiki_config;
	 
	/**
	 * 생성자
	 */
	public function __construct() {
		$this->wiki_config = &wiki_class_load("Config");
	}
	
	/**
	 * 
	 * 프로퍼티(property) 함수
	 * @param string $key 프로퍼티 필드
	 * @return mixed {@link NarinConfig}의 narinGlobal[$key] 값
	 */
	public function __get($key)
	{
	    return $this->wiki_config->getGlobal($key);
	}		
	
}

?>
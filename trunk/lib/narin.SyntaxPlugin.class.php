<?
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
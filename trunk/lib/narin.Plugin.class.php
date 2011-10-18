<?

class NarinPlugin extends NarinClass {
	
	protected $plugin_info;	
	
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$class_name = strtolower(substr(get_class($this), 11));
		$this->plugin_info = wiki_plugin_info($class_name);
	}	
	
	/**
	 * 변수 초기화를 위해 사용
	 */
	public function init() {}
	
}
?>
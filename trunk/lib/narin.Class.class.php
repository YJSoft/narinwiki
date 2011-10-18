<?
if (!defined('_GNUBOARD_')) exit;

class NarinClass {
	
	protected $wiki;
	protected $g4;
	protected $member;
	protected $get;
	protected $post;
	protected $sess;
	protected $is_member;
	protected $is_guest;
	protected $is_admin;
	protected $is_wiki_admin;
	protected $config;
	protected $urlencode;
	protected $bo_table;
	protected $folder;
	protected $doc;
	protected $docname;
	protected $board;
	protected $wiki_config;

	/**
	 * Constructor
	 */
	public function __construct() {
  	
  	global $wiki, $g4, $member, $_GET, $_POST, $_SESSION, $doc, $wr_doc, $board, $write, $view, $write_table, $is_member, $is_admin, $is_guest, $is_wiki_admin, $config, $urlencode;
  	
		$this->wiki = &$wiki;
		$this->g4 = &$g4;
		$this->member = &$member;
		$this->get = $_GET;
		$this->post = $_POST;
		$this->sess = $_SESSION;		
		$this->is_member = $is_member;
		$this->is_wiki_admin = $is_wiki_admin;
		$this->is_admin = $is_admin;
		$this->is_guest = $is_guest;
		$this->config = &$config;
		$this->urlencode = $urlencode;
		$this->write = &$write;
		$this->board = &$board;
		$this->bo_table = $wiki[bo_table];	
		if(!$doc && $wr_doc) $doc = $wr_doc;
		list($ns, $docname, $full) = wiki_page_name($doc, $strip=true);
		$this->docname = $docname;
		$this->doc = $full;
		$this->folder = $ns;		
		$this->wiki_config = &wiki_class_load("Config");
	}
	
}

?>
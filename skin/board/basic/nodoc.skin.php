<?
/**
 * 
 * 나린위키 스킨 : 문서 없음 알림 스킨
 *
 * 접근한 문서가 없는 문서일 경우 보여주는 스킨 페이지.
 * 
 * <b>사용 변수</b>
 * - $g4 : 그누보드 global 변수
 * - $wiki : 위키 환경 설정 변수 (narin.config.php, narin.wiki.lib.php 파일 참조)
 * - $folder : 폴더 경로
 * - $docname : 문서명
 * - $doc : 경로를 포함한 문서명
 * - $write_href : 글 작성 URL
 * - $member : 로그인 정보 ($member['mb_id'], $member['mb_level'], $member['mb_nick'] ...)
 * - $is_admin : 그누보드 관리자 인가
 * - $is_wiki_admin : 위키 관리자인가
 * 
 * @package	narinwiki
 * @subpackage skin
 * @license GPL2 (http://narinwiki.org/license)
 * @author	byfun (http://byfun.com)
 * @filesource
 */
 
if (!defined('_GNUBOARD_')) exit;

?>
<h1><?=$docname?></h1>
이 문서는 아직 만들어지지 않았습니다.
<div class="wikiToolbar">
	<span class="button"><a href="javascript:history.go(-1);">뒤로</a></span>
	<span class="button green"><a href="<?=wiki_url()?>">시작페이지</a></span>
	<span class="button red"><a href="<?=$wiki['g4_url']?>/bbs/write.php?bo_table=<?=$bo_table?>&doc=<?=$doc?>">페이지 만들기</a></span>
</div>

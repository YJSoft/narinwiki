<?
/**
 * 
 * 나린위키 스킨 : 에러 출력 스킨
 *
 * 문서/폴더에 대한 권한 없을 때 등 에러메시지를 출력할 때 사용하는 스킨 페이지.
 * 
 * <b>사용 변수</b>
 * - $title : 에러제목
 * - $msg : 메시지 내용
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
<h1><?=$title?></h1>
<?=$msg?>
<div class="wikiToolbar">
	<span class="button"><a href="javascript:history.go(-1);">뒤로</a></span>
	<span class="button green"><a href="<?=wiki_url()?>">시작페이지</a></span>
</div>

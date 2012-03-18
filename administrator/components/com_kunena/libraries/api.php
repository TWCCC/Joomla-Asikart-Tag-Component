<?php
/**
 * @version $Id$
 * Kunena Component
 * @package Kunena
 *
 * @Copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/

// Dont allow direct linking
defined( '_JEXEC' ) or die();

if (class_exists('Kunena')) return;

class Kunena implements iKunena {
	protected static $version = false;
	protected static $version_date = false;
	protected static $version_name = false;
	protected static $version_build = false;

	public static function buildVersion() {
		if (self::$version === false) {
			if ('1.7.2' == '@' . 'kunenaversion' . '@') {
				$changelog = file_get_contents ( KPATH_SITE . '/CHANGELOG.php', NULL, NULL, 0, 1000 );
				preg_match ( '|\$Id\: CHANGELOG.php (\d+) (\S+) (\S+) (\S+) \$|', $changelog, $svn );
				preg_match ( '|~~\s+Kunena\s(\d+\.\d+.\d+\S*)|', $changelog, $version );
			}
			self::$version = ('1.7.2' == '@' . 'kunenaversion' . '@') ? strtoupper ( $version [1] . '-SVN' ) : strtoupper ( '1.7.2' );
			self::$version_date = ('2012-01-31' == '@' . 'kunenaversiondate' . '@') ? $svn [2] : '2012-01-31';
			self::$version_name = ('Omega' == '@' . 'kunenaversionname' . '@') ? 'SVN Revision' : 'Omega';
			self::$version_build = ('5215' == '@' . 'kunenaversionbuild' . '@') ? $svn [1] : '5215';
		}
		return self::$version;
	}

	public static function isSvn() {
		if ('1.7.2' == '@' . 'kunenaversion' . '@') {
			return true;
		}
		return false;
	}

	public static function version() {
		if (self::$version === false) {
			self::buildVersion();
		}
		return self::$version;
	}

	public static function versionDate() {
		if (self::$version_date === false) {
			self::buildVersion();
		}
		return self::$version_date;
	}

	public static function versionName() {
		if (self::$version_name === false) {
			self::buildVersion();
		}
		return self::$version_name;
	}

	public static function versionBuild() {
		if (self::$version_build === false) {
			self::buildVersion();
		}
		return self::$version_build;
	}

	public static function getVersionInfo() {
		$version = new stdClass();
		$version->version = self::version();
		$version->date = self::versionDate();
		$version->name = self::versionName();
		$version->build = self::versionBuild();
		return $version;
	}

	public static function enabled() {
		if (!JComponentHelper::isEnabled ( 'com_kunena', true )) {
			return false;
		}
		$config = self::getConfig();
		return !$config->board_offline;
	}

	public static function installed() {
		return true;
	}

	public static function getConfig() {
		require_once (KPATH_SITE . '/lib/kunena.config.class.php');
		return KunenaFactory::getConfig ();
	}

	public static function getUserAPI() {
		return new KunenaUserAPI();
	}

	public static function getStatsAPI() {
		return new KunenaStatsAPI();
	}

/*
	public static function getForumAPI() {
		return new KunenaForumAPI();
	}

	public static function getPostAPI() {
		return new KunenaPostAPI();
	}
*/
}


class KunenaUserAPI implements iKunenaUserAPI {
	protected $_db = null;
	protected $_my = null;
	protected $_session = null;

	public function __construct() {
		kimport('error');
		$this->_db = JFactory::getDBO ();
		$this->_my = JFactory::getUser ();
		$this->_session = KunenaFactory::getSession( true );
	}

	public static function version() {
		return 0;
	}

	public function getAllowedCategories($userid) {
		if ($userid != $this->_my->id) return;
		return $this->_session->allowed;
	}
	public function getProfile($userid) {
		return KunenaFactory::getUser ( $userid );
	}
	public function getRank($userid) {
		require_once (KPATH_SITE . '/class.kunena.php');

		$profile = KunenaFactory::getUser ( $userid );
		return $profile->getRank ();
	}

	public function getTopicsTotal($userid) {
		$result = $this->getTopics($userid);
		return $result->total;
	}
	public function getTopics($userid, $start = 0, $limit = 10, $search=false) {
		require_once (KUNENA_PATH_FUNCS . '/latestx.php');
		$obj = new CKunenaLatestX('usertopics', 0);
		$obj->user = JUser::getInstance ( $userid );
		$obj->offset = $start;
		$obj->threads_per_page = $limit;
		$obj->getUserTopics();
		$result = new stdClass();
		$result->total = $obj->total;
		$result->messages = $obj->threads;
		return $result;
	}
	public function getPostsTotal($userid) {
		$result = $this->getPosts($userid);
		return $result->total;
	}
	public function getPosts($userid, $start = 0, $limit = 10, $search=false) {
		require_once (KUNENA_PATH_FUNCS . '/latestx.php');
		$obj = new CKunenaLatestX('ownposts', 0);
		$obj->user = JUser::getInstance ( $userid );
		$obj->offset = $start;
		$obj->threads_per_page = $limit;
		$obj->getUserPosts();
		$result = new stdClass();
		$result->total = $obj->total;
		$result->messages = $obj->customreply;
		return $result;
	}
	public function getFavoritesTotal($userid) {
		$result = $this->getFavorites($userid);
		return $result->total;
	}
	public function getFavorites($userid, $start = 0, $limit = 10, $search=false) {
		require_once (KUNENA_PATH_FUNCS . '/latestx.php');
		$obj = new CKunenaLatestX('favorites', 0);
		$obj->user = JUser::getInstance ( $userid );
		$obj->offset = $start;
		$obj->threads_per_page = $limit;
		$obj->getFavorites();
		$result = new stdClass();
		$result->total = $obj->total;
		$result->messages = $obj->threads;
		return $result;
	}
	public function getSubscriptionsTotal($userid) {
		$result = $this->getSubscriptions($userid);
		return $result->total;
	}
	public function getSubscriptions($userid, $start = 0, $limit = 10, $search=false) {
		require_once (KUNENA_PATH_FUNCS . '/latestx.php');
		$obj = new CKunenaLatestX('subscriptions', 0);
		$obj->user = JUser::getInstance ( $userid );
		$obj->offset = $start;
		$obj->threads_per_page = $limit;
		$obj->getSubscriptions();
		$result = new stdClass();
		$result->total = $obj->total;
		$result->messages = $obj->threads;
		return $result;
	}

	public function subscribeThreads($userid, $threads) {
		if ((int)$userid<1 || $userid != $this->_my->id) return;
		$threadlist = $this->parseParam($threads);
		if (!is_array($threadlist) || empty($threadlist)) return;
		$threads = implode(',', $threadlist);

		$this->_session->updateAllowedForums();
		$allowed = $this->_session->allowed;

		// Only subscribe if allowed and not already subscribed
		$query = "SELECT id FROM #__kunena_messages AS m LEFT JOIN #__kunena_subscriptions AS s ON m.thread=s.thread
			WHERE m.id IN ($threads) AND m.parent=0 AND m.catid IN ($allowed) AND m.hold=0 AND m.moved=0 AND s.thread IS NULL";
		$this->_db->setQuery ($query);
		$threads = $this->_db->loadResultArray();
		if (KunenaError::checkDatabaseError() || empty($threads)) return;

		foreach ($threads as $thread) {
			$subquery[] = "(".(int)$thread.",".(int)$userid.")";
		}
		$query = "INSERT INTO #__kunena_subscriptions (thread,userid) VALUES " . implode(',', $subquery);
		$this->_db->setQuery ($query);
		$this->_db->query ();
		if (KunenaError::checkDatabaseError()) return;
		return $this->_db->getAffectedRows ();
	}
	public function unsubscribeThreads($userid, $threads = false) {
		if ((int)$userid<1 || $userid != $this->_my->id) return;
		if ($threads === true) {
			$where = '';
		} else {
			$threadlist = $this->parseParam($threads);
			if (!is_array($threadlist) || empty($threadlist)) return;
			$threads = implode(',', $threadlist);
			$where = ' AND thread IN('.$threads.')';
		}

		$query = "DELETE FROM #__kunena_subscriptions WHERE userid=".(int)$userid . $where;
		$this->_db->setQuery ($query);
		$this->_db->query ();
		if (KunenaError::checkDatabaseError()) return;
		return $this->_db->getAffectedRows ();
	}
	public function subscribeCategories($userid, $catids) {
		// TODO: NOT TESTED!!
		if ((int)$userid<1 || $userid != $this->_my->id) return;
		$catlist = $this->parseParam($catids);
		if (!is_array($catlist) || empty($catlist)) return;
		$catids = implode(',', $catlist);

		$this->_session->updateAllowedForums();
		$allowed = $this->_session->allowed;

		// Only subscribe if allowed and not already subscribed
		$query = "SELECT id FROM #__kunena_categories AS c LEFT JOIN #__kunena_subscriptions_categories AS s ON c.id=s.catid
			WHERE c.id IN ($catids) AND c.id IN ($allowed) AND s.catid IS NULL";
		$this->_db->setQuery ($query);
		echo $query;
		$catids = $this->_db->loadResultArray();
		if (KunenaError::checkDatabaseError() || empty($catids)) return;

		foreach ($catids as $thread) {
			$subquery[] = "(".(int)$thread.",".(int)$userid.")";
		}
		$query = "INSERT INTO #__kunena_subscriptions_categories (catid,userid) VALUES " . implode(',', $subquery);
		$this->_db->setQuery ($query);
		$this->_db->query ();
		if (KunenaError::checkDatabaseError()) return;
		return $this->_db->getAffectedRows ();
	}
	public function unsubscribeCategories($userid, $catids = false) {
		// TODO: NOT TESTED!!
		if ((int)$userid<1 || $userid != $this->_my->id) return;
		if ($catids === true) {
			$where = '';
		} else {
			$catlist = $this->parseParam($catids);
			if (!is_array($catlist) || empty($catlist)) return;
			$catids = implode(',', $catlist);
			$where = ' AND catid IN('.$catids.')';
		}

		$query = "DELETE FROM #__kunena_subscriptions_categories WHERE userid=".(int)$userid . $where;
		$this->_db->setQuery ($query);
		$this->_db->query ();
		if (KunenaError::checkDatabaseError()) return;
		return $this->_db->getAffectedRows ();
	}
	public function favoriteThreads($userid, $threads) {
		if ((int)$userid<1 || $userid != $this->_my->id) return;
		$threadlist = $this->parseParam($threads);
		if (!is_array($threadlist) || empty($threadlist)) return;
		$threads = implode(',', $threadlist);

		$this->_session->updateAllowedForums();
		$allowed = $this->_session->allowed;

		// Only favorite if allowed and not already favorited
		$query = "SELECT id FROM #__kunena_messages AS m LEFT JOIN #__kunena_favorites AS f ON m.thread=f.thread
			WHERE m.id IN ($threads) AND m.parent=0 AND m.catid IN ($allowed) AND m.hold=0 AND m.moved=0 AND f.thread IS NULL";
		$this->_db->setQuery ($query);
		$threads = $this->_db->loadResultArray();
		if (KunenaError::checkDatabaseError() || empty($threads)) return;

		foreach ($threads as $thread) {
			$subquery[] = "(".(int)$thread.",".(int)$userid.")";
		}
		$query = "INSERT INTO #__kunena_favorites (thread,userid) VALUES " . implode(',', $subquery);
		$this->_db->setQuery ($query);
		$this->_db->query ();
		if (KunenaError::checkDatabaseError()) return;
		return $this->_db->getAffectedRows ();

	}
	public function unfavoriteThreads($userid, $threads = false) {
		if ((int)$userid<1 || $userid != $this->_my->id) return;
		if ($threads === true) {
			$where = '';
		} else {
			$threadlist = $this->parseParam($threads);
			if (!is_array($threadlist) || empty($threadlist)) return;
			$threads = implode(',', $threadlist);
			$where = ' AND thread IN('.$threads.')';
		}

		$query = "DELETE FROM #__kunena_favorites WHERE userid=".(int)$userid . $where;
		$this->_db->setQuery ($query);
		$this->_db->query ();
		if (KunenaError::checkDatabaseError()) return;
		return $this->_db->getAffectedRows ();
	}

	private function parseParam($param) {
		if (is_bool($param)) return $param;
		$parsed = array();
		if (is_array($param)) {
			foreach ($param as $id) if ((int)$id > 0) $parsed[] = (int)$id;
		} else {
			if ((int)$param > 0) $parsed[] = (int)$param;
		}
		return $parsed;
	}
}

class KunenaStatsAPI {
	protected $_db = null;
	protected $_session = null;
	protected $_config = null;

	public function __construct() {
		$this->_db = JFactory::getDBO ();
		$this->_session = KunenaFactory::getSession( true );
		$this->_config = KunenaFactory::getConfig ();
		require_once(KUNENA_PATH_LIB . '/kunena.stats.class.php');
		$this->_stats = CKunenaStats::getInstance ( );
	}

	public function getTotalMembers() {
		$this->_stats->loadTotalMembers();
		return $this->_stats->totalmembers;
	}

	public function getTodayOpen() {
		$this->_stats->loadLastDays();
		return $this->_stats->todayopen;
	}

	public function getYesterdayOpen(){
		$this->_stats->loadLastDays();
		return $this->_stats->yesterdayopen;
	}

	public function getTodayAnswer(){
		$this->_stats->loadLastDays();
		return $this->_stats->todayanswer;
	}

	public function getYesterdayAnswer(){
		$this->_stats->loadLastDays();
		return $this->_stats->yesterdayanswer;
	}

	public function getTotalTitles(){
		$this->_stats->loadTotalTopics();
	 	return $this->_stats->totaltitles;
	}

	public function getTotalMessages(){
		$this->_stats->loadTotalTopics();
		return $this->_stats->totalmsgs;
	}

	public function getTotalSections(){
		$this->_stats->loadTotalCategories();
		return $this->_stats->totalsections;
	}

	public function getTotalCats(){
		$this->_stats->loadTotalCategories();
		return $this->_stats->totalcats;
	}

	public function getLastestMember(){
		$this->_stats->loadLastUser();
		return $this->_stats->lastestmember;
	}

	public function getLastestMemberid(){
		$this->_stats->loadLastUser();
		return $this->_stats->lastestmemberid;
	}

	public function getPostersStats($PosterCount) {
		if ((int)$PosterCount<0) return;
		$this->_stats->loadTopPosters((int)$PosterCount);
		return $this->_stats->topposters;
	}

	public function getTopMessage($PosterCount){
		if ((int)$PosterCount<0) return;
		$this->_stats->loadTopPosters((int)$PosterCount);
		return $this->_stats->topmessage;
	}

	public function getProfileStats($ProfileCount) {
		if ((int)$ProfileCount<0) return;
		$this->_stats->loadTopProfiles((int)$ProfileCount);
		return $this->_stats->topprofiles;
	}

	public function getTopProfileHits($ProfileCount) {
		if ((int)$ProfileCount<0) return;
		$this->_stats->loadTopProfiles((int)$ProfileCount);
		return $this->_stats->topprofilehits;
	}

	public function getTopicsStats($TopicCount) {
		if ((int)$TopicCount<0) return;
		$this->_stats->loadTopicStats((int)$TopicCount);
		return $this->_stats->toptitles;
	}

	public function getTopTitlesHits($TopicCount) {
		if ((int)$TopicCount<0) return;
		$this->_stats->loadTopicStats((int)$TopicCount);
		return $this->_stats->toptitlehits;
	}

	public function getTopPollStats($PollCount) {
		if ((int)$PollCount<0) return;
		require_once(KPATH_SITE . '/lib/kunena.poll.class.php');
		$kunena_poll =& CKunenaPolls::getInstance();
		$toppolls = $kunena_poll->get_top_five_polls ( (int)$PollCount );
		return $toppolls;
	}

	public function getTopPollVotesStats($PollCount) {
		if ((int)$PollCount<0) return;
		require_once(KPATH_SITE . '/lib/kunena.poll.class.php');
		$kunena_poll =& CKunenaPolls::getInstance();
		$toppollvotes = $kunena_poll->get_top_five_votes ( (int)$PollCount );
		return $toppollvotes;
	}

	public function getTopThanks($thanksCount) {
		if ((int)$thanksCount<0) return;
		$this->_stats->loadThanksStats((int)$thanksCount);
		return $this->_stats->topuserthanks;
	}

	public function getTopUserThanks($thanksCount) {
		if ((int)$thanksCount<0) return;
		$this->_stats->loadThanksStats((int)$thanksCount);
		return $this->_stats->topthanks;
	}
}

// Legacy support
$file = JPATH_ROOT . '/components/com_kunena/lib/kunena.defines.php';
if (is_file($file))
	require_once ($file);
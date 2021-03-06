<?php
require_once 'liblqfb.class.php';

class Liquidapi implements Liquid {
	
	private $apiserver;
	private $tnt;
	
	function __construct() { // parametri del server e regolazione dell'insistenza.
		global $settings;
		
		$this->apiserver = $settings['LFAPIURL'];
		$this->tnt = $settings['LFMAXTENT'];
	}

	function __destruct() {
	}
	
	function getSomething($what, $querystring) { // la funzione brutale. fa una query.
		$i=0;
		do {
			$draftsurl= $this->apiserver.$what.'?'.$querystring;			
			$draftsjson = file_get_contents($draftsurl,0,null,null);
			$drafts = json_decode($draftsjson, true);
			//print_r($drafts);
			$i++;
		} while ($drafts['status']!='ok' && $i <= $this->tnt);
		
		if ($i < $this->tnt)
			return $drafts['result'];
		elseif ( $drafts['status'] )
			return $drafts['status'];
		else
			return false;
	}
	
	function getDrafts($querystring='') {
		$txts = $this->getSomething('draft', $querystring);
		if ($this->settings['DEBUG']) echo "gettando drafts\n";
		foreach($txts as $txt){
			$res=$this->getInitiativeInfo($txt['initiative_id']);
			$txt['issue_id']=$res['issue_id'];
			$txt['name']=$res['name'];
			$txt['created']=$res['created'];
			$res=$this->getIssueInfo($txt['issue_id']);
			$txt['area_id']=$res['area_id'];
			$res=$this->getAreaInfo($txt['area_id']);
			$txt['area_name']=$res['name'];

			$txn[$txt['id']]=$txt;
		};
		
		krsort($txn);
		
		if (is_array($txn))
			return $txn;
		else
			return false;
	}
	
	function getInitiativeInfo($id)	{ 
	// mah, apiserver: dimmi un po' di questa proposta...
		
		$res = $this->getSomething('initiative', 'initiative_id='.$id);
		
		if ($res)
			return $res[0];
		else
			return false;
	}

	function getIssueInfo($id) { 
	// mah, apiserver: dimmi un po' di questo Tema...
		
		$res = $this->getSomething('issue', 'issue_id='.$id);
		
		if ($res)
			return $res[0];
		else
			return false;
	}

	function getAreaInfo($id) { 
	// mah, apiserver: dimmi un po' di questa proposta...
		
		$res = $this->getSomething('area', 'area_id='.$id);
		
		if ($res)
			return $res[0];
		else
			return false;
	}

	function getApproved($offset, $limit) {
		$qs = 'include_initiatives=true&'.'include_issues=true&';
		$qs .= 'issue_state=finished_with_winner&';
		$qs .= 'initiative_winner=true&';
		$qs .= 'current_draft=true&'.'render_content=html&';
		$qs .= 'limit='.$limit.'&'.'offset='.$offset.'&';
		return $this->getDrafts($qs);		
	}
};
?>

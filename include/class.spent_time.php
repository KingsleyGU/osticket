<?php
/*********************************************************************
    class.spent_time.php

    This class is mainly for keeping record of the time spent on a ticket;

    Min Gu <gumin@spitzeco.dk>

**********************************************************************/
class Spent_time{
	var $ticketId;
	var $staffId;
	var $created;
	var $currentTime;
	var $spent_time;

	function create($staffId,$ticketId,$created)
	{
		if(is_numeric($staffId)&&is_numeric($ticketId))
		{
				$sql = 'INSERT INTO '.SPENT_TIME_TABLE
	            .' SET ticket_id='.db_input($ticketId)
	            .', staff_id='.db_input($staffId)
	            .', created='.db_input(new DateTime($created))
	            .', ended=NOW()'
	            .', seconds=TIME_TO_SEC(TIMEDIFF(ended,created))';
				
				return db_query($sql)&&db_affected_rows() == 1;
					
		}
		return false;
	}
}



?>
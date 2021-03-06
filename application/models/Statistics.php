<?php 
/**
 * exams-database
 * @copyright	Written for Fachschaft Technische Fakultaet Freiburg, Germany and licensed under a Creative Commons Attribution-ShareAlike 3.0 Unported License.
 * @link		https://github.com/aritas1/exams-database/
 * @author		Daniel Leinfelder <mail@aritas.de>
 * @author		William Glover <william@aamu-uninen.de>
 * @version		1.1
 * @since		1.1
 * @todo		-
 */

class Application_Model_Statistics {
	
	public function getEaxamDownlaodsAllUsedYears() {
		$dbTable = new Application_Model_DbTable_Exam();
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT YEAR(`date`) as year FROM `exam_download_statistic_day` group by YEAR(`date`) ORDER BY YEAR(  `date` ) DESC ;")
		->fetchAll()
		;
		
		$res_array = array();
		foreach ($res as $row2) {
			$res_array[] = $row2['year'];
		}
		
		return $res_array;
	}
	
	
	public function getEaxamAllUsedYears() {
		$dbTable = new Application_Model_DbTable_Exam();
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT YEAR(`create_date`) as year FROM `exam` group by YEAR(`create_date`) ORDER BY YEAR(  `create_date` ) DESC ;")
		->fetchAll()
		;
	
		$res_array = array();
		foreach ($res as $row2) {
			$res_array[] = $row2['year'];
		}
	
		return $res_array;
	}
	
	public function getAllDegreeGroups() {
		$dbTable = new Application_Model_DbTable_Exam();
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT * FROM `degree_group` ORDER BY  `degree_group`.`order` ASC ;")
		->fetchAll()
		;
	
	$res_array = array();
		foreach ($res as $row2) {
			$res_array[] = array('name' => $row2['name'], 'value' => $row2['iddegree_group']);
		}
	
		return $res_array;
	}
	
	public function getAllDegrees() {
		$dbTable = new Application_Model_DbTable_Exam();
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT * FROM `degree` ORDER BY  `degree`.`order` ASC ;")
		->fetchAll()
		;

	
		$res_array = array();
		foreach ($res as $row2) {
			$res_array[] = array('name' => $row2['name'], 'value' => $row2['iddegree']);
		}

		return $res_array;
	}
	
	public function getExamAllGroupsUploads($year = -1) {
		$groups = $this->getAllDegreeGroups();
		$res_array = array();
		foreach ($groups as $group) {
			$elemts = $this->getExamUploadsYear($year, $group['value'], true);
			$sum = 0;
			foreach ($elemts as $elemt) {
				$sum += $elemt;
			}
			$res_array[] = $sum; 
		}
		return $res_array;
	}
		
		public function getExamAllDegreesUploads($year = -1) {
			$groups = $this->getAllDegrees();
			$res_array = array();
			foreach ($groups as $group) {
				$elemts = $this->getExamUploadsYear($year, $group['value'], false);
				$sum = 0;
				foreach ($elemts as $elemt) {
					$sum += $elemt;
				}
				$res_array[] = $sum;
			}
		
		//var_dump($res_array);
		//die();
		
		return $res_array;
	}
	
	
	public function getExamUploadsYear($year = -1, $degree = -1, $group = false) {
		/* SELECT  count(*), DATE_FORMAT(create_date, '%Y-%m')  as order_date FROM `exam`
		 GROUP BY order_date
		Order by order_date
		*/
		
		// this should not happen!
		if($year == -1) {
			throw new Exception('Loaded year statistic with a wrong year('.$year.')');
		}
	
		$dbTable = new Application_Model_DbTable_Exam();
	
		
		$where = " WHERE 1=1 ";
		
		if($degree != -1 && $group == false) {
			$where .= " and `degree_iddegree` = " . $degree;
		
		} elseif($degree != -1 && $group == true) {
			$where = " JOIN `degree` ON degree.iddegree = exam.degree_iddegree JOIN degree_group ON degree_group.iddegree_group =  degree.degree_group_iddegree_group WHERE degree_group.iddegree_group = ".$degree." ";
		}
		
		
		
		if(isset($year)) {
			$where .= " and YEAR(create_date) = " . $year;
		}
		
	
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT  count(*) as uploads, DATE_FORMAT(create_date, '%Y-%m')  as order_date, DATE_FORMAT(create_date, '%m') as month, DATE_FORMAT(create_date, '%Y') as year FROM `exam` ".$where." GROUP BY order_date Order by order_date;")
		->fetchAll()
		;
		
		$res_array = array();
		
		for ($i = 0; $i <= 11; $i++) {
			foreach ($res as $row2) {
				if(intval($row2['month']) == $i+1) { $res_array[$i] =  $row2['uploads']; }
			}
		}
		
		for ($i = 0; $i <= 11; $i++) {
			if(!isset($res_array[$i])) {
				$res_array[$i] = "";
			}
		}
		
		ksort($res_array);
	
		return $res_array;
	}
	
	
	public function getExamUploadsYearByType($year = -1, $degree = -1, $group = false) {
	
		// this should not happen!
		if($year == -1) {
			throw new Exception('Loaded year statistic with a wrong year('.$year.')');
		}
	
		$dbTable = new Application_Model_DbTable_Exam();
	
	
		// dummy, so we can add abetrary where statements starting with add
		$where = " WHERE 1=1 ";
	
		if($degree != -1 && $group == false) {
			$where .= " and `degree_iddegree` = " . $degree;
	
		} elseif($degree != -1 && $group == true) {
			$where = " JOIN `degree` ON degree.iddegree = exam.degree_iddegree JOIN degree_group ON degree_group.iddegree_group =  degree.degree_group_iddegree_group WHERE degree_group.iddegree_group = ".$degree." ";
		}
	
	
	
		if(isset($year)) {
			$where .= " and YEAR(create_date) = " . $year;
		}
	
	
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT  count(*) as uploads, idexam_type,  DATE_FORMAT(create_date, '%Y-%m')  as order_date, DATE_FORMAT(create_date, '%m') as month, DATE_FORMAT(create_date, '%Y') as year FROM `exam` JOIN exam_type ON exam.exam_type_idexam_type = exam_type.idexam_type  ".$where." GROUP BY exam_type_idexam_type, order_date Order by order_date;")
		->fetchAll()
		;
	
		$res_array = array();
		
		
		$amey = new Application_Model_ExamTypeMapper();
		$all = $amey->fetchAll();
		

		// collect all ids
		$ids = array();
		foreach ($all as $x) {
			$ids[] = $x->getId();
		}
	
		foreach ($ids as $id) {
			foreach ($res as $row2) {
				if($id == intval($row2['idexam_type'])) {
				$res_array[$id][] = array(
						'uploads' =>  intval($row2['uploads']),
						'type' =>  intval($row2['idexam_type']),
						'month' =>  intval($row2['month']));
				}
			}
		}
		
		
	
		// define a custom month sort
		function cmp($a, $b)
		{
			if ($a['month'] == $b['month']) {
				return 0;
			}
			return ($a['month'] < $b['month']) ? -1 : 1;
		}
		
		// fill empty types
		foreach ($ids as $id) {
		for ($i = 1; $i <= 12; $i++) {
			$found = false;
			if(isset($res_array[$id])) {
				foreach ($res_array[$id] as $a) {
					if($a['month'] == $i) {
						$found = true;
					}
				}
				}
			
			if(!$found) {
				$res_array[$id][] = array(
					'uploads' =>  0,
					'type' =>  $id,
					'month' =>  $i);
			}
		}
		
		usort($res_array[$id], "cmp");
		}
		
	
		return $res_array;
	}
	
	public function getCourseDownloadsDailyYear($year = -1, $course) {
	
		$dbTable = new Application_Model_DbTable_Exam();
	
	
		// dummy, so we can add abetrary where statements starting with add
		$where = " WHERE 1=1 ";
	
		if($course != -1) {
			$where = "JOIN exam ON exam.idexam = exam_download_statistic_day.exam_idexam JOIN exam_has_course ON exam_has_course .exam_idexam = exam.idexam JOIN course ON exam_has_course .course_idcourse = course.idcourse WHERE idcourse = " . $course;
		}
	
		if(isset($year)) {
			$where .= " and YEAR(date) = " . $year;
		}
	
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT
				sum(exam_download_statistic_day.downloads) as downl,
				DATE_FORMAT(date, '%Y-%m-%d')  as order_date,
				DATE_FORMAT(date, '%j') as days,
				DATE_FORMAT(date, '%d') as day,
				DATE_FORMAT(date, '%m') as month,
				DATE_FORMAT(date, '%Y') as year
	
				FROM `exam_download_statistic_day`
				".$where."
				GROUP BY order_date
				ORDER BY order_date;")
					->fetchAll()
					;
	
		$res_array = array();
	
		foreach ($res as $row2) {
			$res_array[intval($row2['days'])] = intval($row2['downl']);
				
		}
	
		for ($i = 0; $i < 365; $i++) {
			if(!isset($res_array[$i])) {
				$res_array[$i] = 0;
			}
		}
	
	
		ksort($res_array);
	
		return $res_array;
	
	}
	
	
	public function getExamDownloadsDailyYear($year = -1, $degree = -1, $group = false) {
		
		$dbTable = new Application_Model_DbTable_Exam();
		
		
		// dummy, so we can add abetrary where statements starting with add
		$where = " WHERE 1=1 ";
		
		if($degree != -1 && $group == false) {
			$where = " JOIN exam ON exam.idexam = exam_download_statistic_day.exam_idexam WHERE degree_iddegree = " . $degree;
		
		} elseif($degree != -1 && $group == true) {
			$where = " JOIN exam ON exam.idexam = exam_download_statistic_day.exam_idexam JOIN `degree` ON degree.iddegree = exam.degree_iddegree JOIN degree_group ON degree_group.iddegree_group =  degree.degree_group_iddegree_group WHERE degree_group.iddegree_group = ".$degree." ";
		}
		
		
		
		if(isset($year)) {
			$where .= " and YEAR(date) = " . $year;
		}
		
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT
				sum(exam_download_statistic_day.downloads) as downl,
				DATE_FORMAT(date, '%Y-%m-%d')  as order_date, 
				DATE_FORMAT(date, '%j') as days,
				DATE_FORMAT(date, '%d') as day,
				DATE_FORMAT(date, '%m') as month, 
				DATE_FORMAT(date, '%Y') as year

				FROM `exam_download_statistic_day` 
				".$where."
				GROUP BY order_date
				ORDER BY order_date;")
		->fetchAll()
		;
		
		$res_array = array();
		
		foreach ($res as $row2) {
			$res_array[intval($row2['days'])] = intval($row2['downl']);
			
		}
		
		for ($i = 0; $i < 365; $i++) {
			if(!isset($res_array[$i])) {
				$res_array[$i] = 0;
			}
		}
		
		
		ksort($res_array);
		
		return $res_array;
	
	}
	
	// returns the daily downloads for a single Exam
	public function getExamDownloadsDailyYear2($year = -1, $exam = -1) {
	
		$dbTable = new Application_Model_DbTable_Exam();
	
	
		// dummy, so we can add abetrary where statements starting with add
		$where = " WHERE 1=1 ";
	
		if($exam != -1) {
			$where .= " and exam_download_statistic_day.exam_idexam  = " . $exam;
	
		}
	
	
	
		if(isset($year)) {
			$where .= " and YEAR(date) = " . $year;
		}
	
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT
				sum(exam_download_statistic_day.downloads) as downl,
				DATE_FORMAT(date, '%Y-%m-%d')  as order_date,
				DATE_FORMAT(date, '%j') as days,
				DATE_FORMAT(date, '%d') as day,
				DATE_FORMAT(date, '%m') as month,
				DATE_FORMAT(date, '%Y') as year
	
				FROM `exam_download_statistic_day`
				".$where."
				GROUP BY order_date
				ORDER BY order_date;")
					->fetchAll()
					;
	
		$res_array = array();
	
		foreach ($res as $row2) {
			$res_array[intval($row2['days'])] = intval($row2['downl']);
				
		}
	
		for ($i = 0; $i < 365; $i++) {
			if(!isset($res_array[$i])) {
				$res_array[$i] = 0;
			}
		}
	
	
		ksort($res_array);
	
		return $res_array;
	
	}
	
	
	
	public function getExamDownloadsRankingYear($year = -1, $degree = -1, $group = false) {
	
		// this should not happen!
		if($year == -1) {
			throw new Exception('Loaded year statistic with a wrong year('.$year.')');
		}
		
		$dbTable = new Application_Model_DbTable_Exam();
	
	
		// dummy, so we can add abetrary where statements starting with add
		$where = " WHERE 1=1 ";
	
		if($degree != -1 && $group == false) {
			$where .= " and degree_iddegree = " . $degree;
	
		} elseif($degree != -1 && $group == true) {
			$where = " JOIN `degree` ON degree.iddegree = exam.degree_iddegree JOIN degree_group ON degree_group.iddegree_group =  degree.degree_group_iddegree_group WHERE degree_group.iddegree_group = ".$degree." ";
		}
	
	
	
		if(isset($year)) {
			$where .= " and YEAR(date) = " . $year;
		}
	
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT
				sum(exam_download_statistic_day.downloads) as downl,
				idexam,
				YEAR(date) as year
	
				FROM `exam_download_statistic_day`
				JOIN exam ON exam.idexam = exam_download_statistic_day.exam_idexam
				".$where."
				GROUP BY year, idexam
				ORDER BY downl DESC;")
					->fetchAll()
					;
	
		$res_array = array();
	
		$rank = 1;
		foreach ($res as $row2) {
			$res_array[] = array('downloads' => intval($row2['downl']), 'idexam' => intval($row2['idexam']), 'rank' => $rank);
			$rank++;				
		}
		
		return $res_array;
	
	}
	
	public function getCoursDownloadsRankingYear($year = -1, $course = -1, $group = false) {
	
		// this should not happen!
		if($year == -1) {
			throw new Exception('Loaded year statistic with a wrong year('.$year.')');
		}
	
		$dbTable = new Application_Model_DbTable_Exam();
	
	
		// dummy, so we can add abetrary where statements starting with add
		$where = " WHERE 1=1 ";
		$group = " GROUP BY year, idcourse";
	
		if($course != -1) {
			$where .= " and idcourse = " . $course;
			$group = "GROUP BY year, idexam";
		}
	
	
	
		if(isset($year)) {
			$where .= " and YEAR(date) = " . $year;
		}
	
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT
				sum(exam_download_statistic_day.downloads) as downl,
				idcourse,
				idexam,
				course.name as cname,
				YEAR(date) as year
	
				FROM course
				JOIN exam_has_course ON course.idcourse = exam_has_course.course_idcourse
				JOIN exam ON exam.idexam = exam_has_course.exam_idexam
				JOIN exam_download_statistic_day ON exam.idexam = exam_download_statistic_day.exam_idexam
				".$where."
				".$group."
				ORDER BY downl DESC;")
					->fetchAll()
					;
	
		$res_array = array();
	
		$rank = 1;
		foreach ($res as $row2) {
			$res_array[] = array('downloads' => intval($row2['downl']), 'idcourse' => intval($row2['idcourse']), 'idexam' => intval($row2['idexam']), 'cours_name' => $row2['cname'], 'rank' => $rank);
			$rank++;
		}
	
		return $res_array;
	
	}
	
	
	public function getCoursDownloadsRankingGroupsYear($year = -1, $degree = -1, $group = false) {
	
		// this should not happen!
		if($year == -1) {
			throw new Exception('Loaded year statistic with a wrong year('.$year.')');
		}
	
		$dbTable = new Application_Model_DbTable_Exam();
	
	
		// dummy, so we can add abetrary where statements starting with add
		$where = " WHERE 1=1 ";
	
		if($degree != -1 && $group == false) {
			$where .= " and degree_iddegree = " . $degree;
	
		} elseif($degree != -1 && $group == true) {
			$where = " JOIN `degree` ON degree.iddegree = exam.degree_iddegree JOIN degree_group ON degree_group.iddegree_group =  degree.degree_group_iddegree_group WHERE degree_group.iddegree_group = ".$degree." ";
		}
	
	
	
		if(isset($year)) {
			$where .= " and YEAR(date) = " . $year;
		}
	
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT
				sum(exam_download_statistic_day.downloads) as downl,
				idcourse,
				idexam,
				course.name as cname,
				YEAR(date) as year
	
				FROM course
				JOIN exam_has_course ON course.idcourse = exam_has_course.course_idcourse
				JOIN exam ON exam.idexam = exam_has_course.exam_idexam
				JOIN exam_download_statistic_day ON exam.idexam = exam_download_statistic_day.exam_idexam
				".$where."
				GROUP BY year, idcourse
				ORDER BY downl DESC;")
					->fetchAll()
					;
	
		$res_array = array();
	
		$rank = 1;
		foreach ($res as $row2) {
			$res_array[] = array('downloads' => intval($row2['downl']), 'idcourse' => intval($row2['idcourse']), 'idexam' => intval($row2['idexam']), 'cours_name' => $row2['cname'], 'rank' => $rank);
			$rank++;
		}
	
		return $res_array;
	
	}
	
	public function getExamExamination($course, $year = -1) {
		
		$dbTable = new Application_Model_DbTable_Exam();
		
		$where = " WHERE 1=1 ";
		
		if($course != -1) {
			$where .= " and idcourse = " . $course;
		
		}
		
		if($year != -1) {
			$where .= " and YEAR(examination_date) = " . $year;
		}
		
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT comment,
				DATE_FORMAT(examination_date, '%j') as days,
				examination_date,
				idcourse_examination as id
				FROM  `course_examination`
				".$where."
				;")
						->fetchAll();
		
		$res_array = array();
		
		foreach ($res as $row2) {
			$res_array[] = array('id' => $row2['id'], 'days' => $row2['days'], 'comment' => $row2['comment'], 'examination_date' => $row2['examination_date']);
		}
		
		return $res_array;
	
	}
	
	public function getExamExaminationByExam($exam = -1, $year = -1) {
	
		if($exam == -1) {
			throw new Exception("no real exam id given");
		}
		
		$dbTable = new Application_Model_DbTable_Exam();
	
		$where = " WHERE 1=1 ";
	
		if($exam != -1) {
			$where .= " and exam.idexam = " . $exam;
	
		}
	
		if($year != -1) {
			$where .= " and YEAR(examination_date) = " . $year;
		}
	
		$res = $dbTable
		->getDefaultAdapter()
		->query("SELECT course_examination.comment as comment,
				DATE_FORMAT(examination_date, '%j') as days
				FROM  `course_examination`
				JOIN course ON course.idcourse = course_examination.`idcourse`
				JOIN exam_has_course ON exam_has_course.course_idcourse = course.idcourse
				JOIN exam ON exam_has_course.exam_idexam = exam.idexam
				".$where."
				;")
					->fetchAll();
	
		$res_array = array();
	
		foreach ($res as $row2) {
			$res_array[] = array('days' => $row2['days'], 'comment' => $row2['comment']);
		}
	
		return $res_array;
	
	}
	
}
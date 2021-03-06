<?php
/**
 * Debug output
 * 
 * @author Gerd Riesselmann
 * @ingroup View
 */
class WidgetDebugBlock implements IWidget {
	public static function output() {
		$w = new WidgetDebugBlock();
		return $w->render();
	}
	
	public function render($policy = self::NONE) {
		$out = '';
		if (Config::has_feature(Config::TESTMODE)) {
			$out .= html::h('Debug Block', 2);
			if (!Config::has_feature(Config::DISABLE_CACHE)) {
				$out .= html::warning('Cache is enabled!');
			}
			$out .= $this->render_properties();
			
			$sections = array(
				'Slow and Failed Queries' => $this->render_db_queries(true),
				'Templates'  => $this->render_templates(),
				'DB Queries' => $this->render_db_queries(),
			);
			// Alow modules extending sections
  			EventSource::Instance()->invoke_event('debugblock', 'sections', $sections);
			
			foreach($sections as $heading => $content) {
				if ($content) {
					$out .= html::h(GyroString::escape($heading), 3);
					$out .= $content;
				}
			}

			$out = html::div($out, 'debugblock');
		}
		
		return $out;
	}

	protected function render_properties() {
	   	$endtime = microtime(true);
		$modules = Load::get_loaded_modules();
		$count_queries = count(DB::$query_log);
		$debugs = array(
	  		'Memory' => GyroString::number(memory_get_usage()/1024, 2) . ' KB',
	  		'Memory Peak' => GyroString::number(memory_get_peak_usage()/1024, 2) . ' KB',
	  		'Execution time' => $this->duration($endtime - APP_START_MICROTIME),
	   		'DB-Queries execution time' => $this->duration(DB::$queries_total_time),
			'DB-Queries' => $count_queries,
			'DB-Queries average time' => $this->duration(DB::$queries_total_time / max($count_queries, 1)),
			'DB connect time' => $this->duration(DB::$db_connect_time),
	  		'PHP-Version' => phpversion(),
			'Generated' => GyroDate::local_date(time()),
	   		'Modules' => (count($modules) > 0) ? implode(', ', $modules) : '-' 
  		);
  		
  		// Alow modules extending props
  		EventSource::Instance()->invoke_event('debugblock', 'properties', $debugs);
  		
  		// Output
	  	$li = array();
		foreach($debugs as $key => $value) {
			$li[] = html::b($key . ':') . ' ' . $value;
		}
		return html::li($li);
	}

	protected function render_db_queries($only_problems = false) {
		$out = '';
		// Query Logs
		if (count(DB::$query_log)) {
			$table = '';
			$c = 0;
			foreach(DB::$query_log as $query) {
				$is_problem = !$query['success'] || ($query['seconds'] > Config::get_value(Config::DB_SLOW_QUERY_THRESHOLD));
				if (!$only_problems || $is_problem) {
					$table .= $this->render_db_query_times($query);
					$table .= $this->render_db_query_message($query);
					$table .= $this->render_db_query_explain($query);
				}
			}
			if ($table) {
				$out .= html::tag('table', $table, array('summary' => 'List of all issued DB queries'));
			}
		}
		return $out;
	}
	
	protected function render_db_query_times($query) {
		$is_slow = ($query['seconds'] > Config::get_value(Config::DB_SLOW_QUERY_THRESHOLD));
		$cls = $query['success'] ? 'query ok' : 'query error';
		
		$query_time = array(
			$this->msec($query['seconds']),
			$this->sec($query['seconds']),
		);
		if ($is_slow) {
			$cls .= ' slow';
			$query_time[] = 'Slow!';
		}
		
		return html::tr(
			array(
				html::td(html::b(implode('<br />', $query_time))),
				html::td(GyroString::escape($query['query'])),							
			),
			array('class' => $cls)
		);
	}
	
	protected function render_db_query_message($query) {
		$ret = '';
		if ($query['message']) {
			$ret .= html::tr(
				html::td(GyroString::escape($query['message']), array('colspan' => 2)),
				array('class' => 'query message')
			);
		}
		return $ret;
	}
	
	protected function render_db_query_explain($query) {
		$ret = '';
		$is_slow = ($query['seconds'] > Config::get_value(Config::DB_SLOW_QUERY_THRESHOLD));
		if ($is_slow) {
			$sql = $query['query'];
			$result = DB::explain($query['query'], $query['connection']);
			if ($result) {
				$ret .= html::tr(
					html::td($this->render_db_query_explain_result($result), array('colspan' => 2)),
					array('class' => 'query explain')
				);
			}
		}
		return $ret;
	}
	
	protected function render_db_query_explain_result(IDBResultSet $result) {
		$rows = array();
		$head = false;
		while($row = $result->fetch()) {
			if ($head === false) {
				$head = array();
				foreach(array_keys($row) as $h) {
					$head[] = html::td(GyroString::escape($h), array(), true);
				}
			}
			
			$tr = array();
			foreach($row as $col => $value) {
				$tr[] = html::td(GyroString::escape($value));	
			}
			$rows[] = $tr;
		}
		$table = html::table($rows, $head, 'Explain Result', array('class' => 'full'));
		return $table;
	}
	
	protected function render_templates() {
		$out = '';
		// Template logs
		if (count(TemplatePathResolver::$resolved_paths)) {
			$table = ''; 
			foreach(TemplatePathResolver::$resolved_paths as $resource => $file) {
				$cls = 'template';
				$table .= html::tr(
					array(
						html::td(GyroString::escape($resource)),
						html::td(GyroString::escape($file)),
					),
					array('class' => $cls)
				);
			}
			$out .= html::tag('table', $table, array('summary' => 'Mapping of template ressources to files'));
		}
		return $out;
	}

	protected function render_translations() {
		$out = '';
		// Translation logs
		if (count(Translator::Instance()->groups)) {
			$out .= html::h('Translations', 3);
			
			$li = array(); 
			foreach(Translator::Instance()->groups as $key => $group) {
				if (count($group)) {
					$li[] = GyroString::escape($key);
				}
			}
			$out .= html::li($li, 'translations');
		}
		return $out;	
	}
	
	protected function sec($sec) {
		return GyroString::number($sec, 4) . '&nbsp;sec';
	}

	protected function msec($sec) {
		return GyroString::number($sec * 1000, 2) . '&nbsp;msec';
	}
	
	protected function duration($sec) {
		return $this->msec($sec) . ' - ' . $this->sec($sec);
	}
}

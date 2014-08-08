<?php

	function hn_ts_visualizationLoader()
	{
		$visDir = HN_TS_PLUGIN_DIR . '/visualisations/';
		$visDirs = glob($visDir . "*", GLOB_ONLYDIR);
	
		?>
		<br>
		<br>
		<table class="form-table">
			<form method="post" action="#" enctype="multipart/form-data">
				<tr>
					<h2>Load your own visualisation</h2>
				</tr>
				<hr>
				<tr>
					<td>Visualisation name</td>
					<td>
						<input name="name" type="text">
					</td>
				</tr>
				<tr>
					<td>HTML file</td>
					<td>
						<input name="html_file" id="html_file" type="file">
					</td>
				</tr>
				<tr>
					<td>CSS file</td>
					<td>
						<input name="css_file" id="css_file" type="file">
					</td>
				</tr>
				<tr>
					<td>Javascript file</td>
					<td>
						<input name="js_file" id="js_file" type="file">
					</td>
				</tr>
				<tr>
					<td>
						<hr>
						<button type="submit" id="upload-button" class="button-primary">
							Upload
						</button>
					</td>
				</tr>	
			</form>
		</table>
		<?php
		error_reporting(E_ALL);
		if(isset($_POST)){
			if (isset($_POST["name"]) && isset($_FILES["html_file"]) && isset($_FILES["css_file"]) && isset($_FILES["js_file"])) {
				$name = hn_ts_sanitise($_POST["name"]);
				$fldr = $visDir . $name;
				mkdir($fldr,0777,true);
				move_uploaded_file($_FILES["html_file"]["tmp_name"], $fldr .'/viz.php');
				move_uploaded_file($_FILES["css_file"]["tmp_name"], $fldr .'/'. $_FILES["css_file"]["name"]);
				move_uploaded_file($_FILES["js_file"]["tmp_name"], $fldr .'/'. $_FILES["js_file"]["name"]);

			}
			else{
				$GLOBALS['DebugMyPlugin']->panels['main']->addMessage('no file','');
				return;
			}
		}else{
			return;

		}
	}

	function hn_ts_sanitise($arg){
		if(isset($arg)){
			return preg_replace('/[^-a-zA-Z0-9_\s:\/.]/', '_', (string)$arg);
		}else{
			return null;
		}
	}

?>
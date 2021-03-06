<!DOCTYPE html>
<html>
	<head>
		<title></title>
	</head>
	<body>
		<?php
			include 'database.php';

			//Users must come from uploadSong.php
			if(!isset($_POST["submit"]))
			{
				echo "GTFO";
				exit();
			}

			$fileTypes    = array("mp3");								//Accepted file types to be uploaded
			$target_dir   = "/home/pi/Music/";							//Folder all music will be placed in
			$uploadOk     = true;											//If all data was entered correctly

			if(!isset($_POST["artist"]))
			{
				echo "Your song must have a artist<br>";

				$uploadOk = false;
			}

			if(!isset($_POST["genre"]))
			{
				echo "Your song must have a genre<br>";

				$uploadOk = false;
			}

			if(!$uploadOk)
				exit();

			for($i = 0; $i < count($_FILES['files']['name']); $i++)
			{
				$songName     = $_FILES["files"]["name"][$i];
				$songFileType = pathinfo(basename($_FILES["files"]["name"][$i]),PATHINFO_EXTENSION);

				//Checks valid file tpye
				if(!in_array($songFileType, $fileTypes))
				{
					echo "Song: " . $songName . " was not a ";

					foreach ($fileTypes as $type) 
						echo $type . " ";

					echo "<br>";
					
					continue;
				}

				$artistExists = true;											
				$genreExists  = true;

				//set complete save path for song
				$artist      = strtolower($_POST["artist"]);
				$artistPath  = $target_dir . $artist;
				$target_file = $target_dir . "$artist/" . basename($_FILES["files"]["name"][$i]);
				$genre       = strtolower($_POST["genre"]);

				if(!is_dir($artistPath))
				{
					$couldCreateDir = mkdir($artistPath);
					$artistExists = false;

					if(!$couldCreateDir)
					{
						echo "Could not create artist directory";
						exit();
					}
				}
				
				if(file_exists($target_file))
				{
					echo "Song: " . $songName . " has already been uploaded! <br>";
					continue;
				}

				$query = "SELECT idGenere FROM Genere WHERE genereName = '$genre'";
				$result = mysqli_query($con, $query);

				if (mysqli_num_rows($result) == 0) 
				    $genreExists = false;

				if(!$artistExists)
				{
					$query = "INSERT INTO Artist (artistName) VALUES ('$artist')";
					$result = mysqli_query($con, $query);
				}

				if(!$genreExists)
				{
					$query = "INSERT INTO Genere (genereName) VALUES ('$genre')";
					$result = mysqli_query($con, $query);
				}

				//Fancy Query
				$query = "INSERT INTO Song (artist, genere, songName) VALUES ((SELECT idArtist FROM Artist WHERE artistName = '$artist'), (SELECT idGenere FROM Genere WHERE genereName = '$genre'), '$songName')";
				$result = mysqli_query($con, $query);
				if(!$result)
				{
					echo "Song: " . $songName . " could not be inserted into the database<br>";
					continue;
				}

				if(move_uploaded_file($_FILES["files"]["tmp_name"][$i], $target_file))
					echo "Song: " . $songName . " was uploaded sucuessfully<br>";
				else
					echo "Song: " . $songName . " was not uploaded sucuessfully<br>";
			}

		?>

		<button type="button" class="btn btn-default" onclick="window.location.replace('uploadSong.php');">Return to Upload</button>
	</body>
</html>	
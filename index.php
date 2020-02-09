<?php
    require_once 'vendor/autoload.php';
    require_once "random_string.php";

    use MicrosoftAzure\Storage\Blob\BlobRestProxy;
    use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
    use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
    use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
    use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

    $connectionString = "AccountName=macdstorage;AccountKey=u6bmfai3WQKPRxDfHR4iL0c1g8CZlNDywVahvdR5tqIBFHhCizCgwSviVXIj+mabLkPL/WndWLA7jvlwRWEMXQ==";
    
    $blobClient = BlobRestProxy::createBlobService($connectionString);

    $containerName = "macdfinalsubmission";

    if (isset($_POST['submit'])) {

        $fileToUpload = $_FILES["fileToUpload"]["name"];
        $content = fopen($_FILES["fileToUpload"]["tmp_name"], "r");
        echo fread($content, filesize($fileToUpload));

        $blobClient->createBlockBlob($containerName, $fileToUpload, $content);
        header("Location: index.php");
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>MACD Submission - Azure Computer Vision</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.4.1.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>    
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script type="text/javascript">
        function processImage() {
            // **********************************************
            // *** Update or verify the following values. ***
            // **********************************************
    
            // Replace <Subscription Key> with your valid subscription key.
            var subscriptionKey = "283ca858319a4abda0d161d035edeca3";
    
            // You must use the same Azure region in your REST API method as you used to
            // get your subscription keys. For example, if you got your subscription keys
            // from the West US region, replace "westcentralus" in the URL
            // below with "westus".
            //
            // Free trial subscription keys are generated in the "westus" region.
            // If you use a free trial subscription key, you shouldn't need to change
            // this region.
            var uriBase =
                "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";
    
            // Request parameters.
            var params = {
                "visualFeatures": "Categories,Description,Color",
                "details": "",
                "language": "en",
            };
    
            // Display the image.
            var sourceImageUrl = document.getElementById("inputImage").value;
            document.querySelector("#sourceImage").src = sourceImageUrl;

            // Make the REST API call.
            $.ajax({
                url: uriBase + "?" + $.param(params),
    
                // Request headers.
                beforeSend: function(xhrObj){
                    xhrObj.setRequestHeader("Content-Type","application/json");
                    xhrObj.setRequestHeader(
                        "Ocp-Apim-Subscription-Key", subscriptionKey);
                },
    
                type: "POST",
    
                // Request body.
                data: '{"url": ' + '"' + sourceImageUrl + '"}',
            })
    
            .done(function(data) {
                // Show formatted JSON on webpage.
                $("#responseTextArea").val(JSON.stringify(data, null, 2));
                console.log(sourceImageUrl);
            })
    
            .fail(function(jqXHR, textStatus, errorThrown) {
                // Display error message.
                var errorString = (errorThrown === "") ? "Error. " :
                    errorThrown + " (" + jqXHR.status + "): ";
                errorString += (jqXHR.responseText === "") ? "" :
                    jQuery.parseJSON(jqXHR.responseText).message;
                alert(errorString);
            });
        };
    </script>

</head>
    
    <body>
        <div class="container">
            <div class="row">
                <div class="col">
                    <h1 class="text-center">Analisa Gambar dengan Azure Computer Vision</h1>
                    <hr>
                    <p>Pilih gambar yang akan di Analisa.</p>
                    <form action="index.php" method="post" enctype="multipart/form-data">
                        <input type="file" name="fileToUpload" accept=".jpg, .jpeg, .png">
                        <input type="submit" name="submit" value="Upload" class="btn btn-primary">
                    </form>
                </div>
            </div>
            <div class="row mt-5">
                <table class="table">
                    <thead class="table-info">
                        <tr>
                            <td scope="col">Nama</td>
                            <td scope="col">URL</td>
                            <td scope="col">Aksi</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $listBlobsOptions = new ListBlobsOptions();
                        $listBlobsOptions->setPrefix("");

                        $no = 0;
						do {
                            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
							foreach ($result->getBlobs() as $blob) {
                                if (++$no == 2) break;
						?>
                        <tr>
                            <td><?= $blob->getName() ?></td>
                            <td><?= $blob->getUrl() ?></td>
                            <td>
                                <!--<input type="hidden" name="inputImage" />
                                <input type="submit" name="submit" value="Analisa" class="btn btn-primary">-->
                                <button id="inputImage" onclick="processImage()" value="<?= $blob->getUrl() ?>" class="btn btn-primary">Analisa</button>
                            </td>
                        </tr>
                        <?php
							} $listBlobsOptions->setContinuationToken($result->getContinuationToken());
						} while($result->getContinuationToken());
						?>
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col">
                    <h1 class="text-center">Hasil Analisa</h1>
                    <hr>
                    <div class="row" style="width:1020px; display:table;">
                        <div class="col" id="jsonOutput" style="width:600px; display:table-cell;">
                            <b>Response:</b><br><br>
                            <textarea id="responseTextArea"
                                style="width:580px; height:400px;" readonly></textarea>
                        </div>
                        <div style="width:420px; display:table-cell;">
                            <b>Source Image:</b><br><br>
                            <img id="sourceImage" width="400" /><br>
                            <h3 id="description">...</h3>
                        </div>
				    </div>
                </div>
            </div>
        </div>
    </body>

</html>

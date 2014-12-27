<!DOCTYPE html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    
    <title><?php $this->vtitle(); ?></title>
    <?= $this->data['headincludes'] ?> 
</head>
<style>
    body{
        color: #222;
    }
</style>

<div class="container">
<h2>Diagnostic panel</h2>
<h4>Crystal Framework v<?= FRAMEWORK_VER ?></h4>
<hr>

    <div class="col-md-4" style="background-color: #D9EDF7; border-radius: 5px;">
        <h3>Environment</h3>
        <table class="table">
            <tr>
                <td>PHP Version</td>                
                <td><?= phpversion(); ?></td>
                <td><span class="label label-success">PASS</span></td>
            </tr>
            <tr>
                <td>PDO Drivers</td>                
                <td><?= implode('; ', pdo_drivers()); ?></td>
                <td><span class="label label-danger">FAIL</span></td>               
            </tr>
            <tr>
                <td>Operating system</td>                
                <td><?= PHP_OS ?></td>
                <td><span class="label label-success">PASS</span></td>
            </tr>
            <tr>
                <td>PHP directory separator</td>                
                <td><?= DIRECTORY_SEPARATOR ?></td>
                <td><span class="label label-success">PASS</span></td>
            </tr>
            
            
        </table>
    </div>
</div>
<br>
<footer>
    <div class="container">
        <div class="alert alert-info">
            Copyright &COPY; Krystian Biela 2014 
        </div>
    </div>
</footer>



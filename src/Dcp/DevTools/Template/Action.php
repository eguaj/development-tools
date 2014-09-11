<?php
namespace Dcp\DevTools\Template;

class Action extends Template
{

    public function render($arguments, $outputPath, $force = false)
    {
        if (!empty($outputPath) && !is_dir($outputPath)) {
            throw new Exception("The output path $outputPath is not a dir");
        }
        if (!isset($arguments["name"]) || !$this->checkLogicalName($arguments["name"])) {
            throw new Exception("You need to set the name of the action with a valid name ".$this->logicalNameRegExp);
        }
        if (isset($arguments["layout"])) {
            $layoutPath = $outputPath . DIRECTORY_SEPARATOR . "Layout". DIRECTORY_SEPARATOR;
            if (!is_dir($outputPath)) {
                mkdir($outputPath);
            }
            $arguments["layoutFileName"] = strtolower($arguments["name"]) . ".html";
            $layoutPath .= $arguments["layoutFileName"];
            parent::render("action_layout", $arguments, $layoutPath, $force);
        }

        if (isset($arguments["script"])) {
            $scriptPath = $outputPath . strtolower("action." . $arguments["name"]) . ".php";
            $arguments["script_name"] = strtolower($arguments["name"]);
            var_export($scriptPath);
            parent::render("action_script", $arguments, $scriptPath, $force);
        }

        return parent::render("action", $arguments, false);
    }

} 
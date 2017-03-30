Plugin install
--------------

1. create folder under Plugin folder with your plugin code (ex: Plugin/Paginator)
2. the namespace for your plugin will be Plugin\Paginator (ex: namespace Plugin\Paginator;)
3. your must have config.php file that will return an array of configuration (plugin events must be unde "plugin-manager" key, nut you can also use app keys to overwrite some as dependencies or tempaltes)
4. if you need to add something to admin then your plugin must attach to specific events:
    - create a new template under templates/plugin folder
    - add this line at the very begining of your tempalte {% extends __template__[_self.getTemplateName()|e]%}
    - use \Plugin\Utils::changeTemplate($Params, '[your_template]'); in your plugin class
5. 
# define base tag ie: <base href="theme/startbootstrap-clean-blog/" target="_blank">
# define theme pages list ie: index.html.twig, about.html.twig , subfolder/test.html.twig
# define theme public

maybe is ok to have this structure [] - folder
[theme name]
- [public] 
- - - [css]
- - - [js]
- - - [img]
- - - [fonts]
- - - [...]
- [templates]
- - - index.html.twig
- - - about.html.twig
- - - post.html.twig
- - - contact.html.twig
content of public will be copied into public/[theme name]/
content of templates will be copied into templates/theme/[theme name]/
# dn-bundle-plugin
Плагин для создания пакетов для DevelNext используя jppm
## Usage
Отредактируйте ваш package.php.yml:
```yaml
devDeps:
  dn-bundle-plugin: '*'
...
plugins:
  - DevelNextBundle
```
Затем используйте эту команду для сборки пакета:
```
jppm bundle:build
```
Вы сможете найти собранный dnbundle по пути **PROJECT_DIRECTORY**/bundle/**PROJECT_NAME**-**PROJECT_VERSION**.dnbundle

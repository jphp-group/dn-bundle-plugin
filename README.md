# dn-bundle-plugin
Plugin for creating bundles for develnext using jppm
## Usage
Edit you package.php.yml like this:
```yaml
devDeps:
  dn-bundle-plugin: '*'
...
plugins:
  - DevelNextBundle
```
Then use this command for create bundle:
```
jppm bundle:build
```
You can find dnbundle file at **PROJECT_DIRECTORY**/bundle/**PROJECT_NAME**-**PROJECT_VERSION**.dnbundle

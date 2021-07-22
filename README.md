# Symfony
## Services autowirig
The infrastructure not included by default, and its services folders *MUST* be manually added, as for example:
```yml
# active-framework/config/services.yaml
App\:
    resource: '../src/*/{...,Infrastructure/{Repository/Redis,Framework/Symfony5}}'
```
This way the app will autowire all classes from the **Redis Repository**, and the **Symfony5 Framework** without risking
to fail when a new type of infrastructure is added that implements the same interfaces. 

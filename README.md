SOAP Yaml
=========

Create the SOAP request by YAML templates.



## Motivación

Como desarrollador de **PHP** en ocasiones nos tocan consumir **SOAP** APIs. Ya existen potentes librerías que nos hacen la vida más fácil a la hora de implementar estas soluciones y no es el objetivo de este proyecto reinventar la rueda.

Pero en ocasiones, muchas más de las que reconocemos, el `WSDL` esta mal definido, tiene errores o depende de un descriptor que ya no existe. Y en ese momento nos sumergimos en la documentación de la librería para intentar solucionar la papeleta de nuestro lado, por que comunicar al equipo de desarrollo del WebService para que corrijan el `WSDL` sería incluso más lento. Los plazos de entregan son los que son y al final te decides por usar la versátil y todo poderosa librería `curl`. Que creo que todo desarrollador venera en silencio.

Este enfoque tiene un problema y es que debes generar de alguna forma el `XML` del `body` de la petición **SOAP**. Al final todos nos decantamos, por que es lo más rápido y sencillo, de hacer una simple concatenación de cadenas de caracteres con sus respectivos valores. A parte de ser poco elegante,  cuanto mayor sea el cuerpo de la llamada SOAP más dificultad y por tanto mayor deuda técnica tendrá tu proyecto.

Existen varias aproximaciones que podríamos usar para corregir esto:

1. Usar una plantilla XML, parsearla, completarla con los valores y volver a generar el XML. No se a ti, pero a mi no me gusta nada, es poco elegante y la he descartado.
2. Usar un **motor de plantillas**, igual que antes usaríamos una plantilla en XML y se la pasaríamos al motor con los correspondientes valores a completar y de esa forma obtendríamos la petición. Por ejemplo en `Symfony` usaríamos `Twig`. Este enfoque es el que considero el más versátil, ya que nos permitiría usar toda la potencia del motor con sus flujos de control, librerías y plugins. Entiendo que los puritanos de la eficiencia, entre los que me encuentro, puedan disentir ya que sin duda es poco eficiente, y coincido con ellos en que desarrollar código eficiente evita que se produzcan cuellos de botella. Pero, como en todo, hay que tener una visión global, que en proyecto sea una solución inviable no significa que en otro sea una solución elegante.
3. Usar **Yaml** como plantilla. Esta es la aproximación que abordo con este proyecto. Una de las características más importantes de **Yaml** es que es más legible que otro formato, como puede ser `XML` y `JSON`, y también es muy fácil de redactar. ¿Cual es la idea que esta detrás de esta aproximación? Es sencillo, con unas simples reglas podemos expresar un `XML` como un `array asociativo` y este a su vez lo podemos almacenar como un documento en `Yaml`, y por tanto podemos hacer el proceso a la inversa.


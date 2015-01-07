Elements are like a plugin, or functional bundle, for the CASH platform. Each one is a standalone 
package containing descriptive metadata, a controller class, view templates, and forms to plug into 
the admin app. Specifically, each element consists of:  

 - A main controller class file 
 - A support directory containing:
   - templates directory for mustache template views for every state (plus admin)
   - admin.php as a controller for the amdin template
   - image.jpg as a header/descriptive image
   - metadata.json containing a version number, friendly name, description, etc

Data for each instance of an element is stored as a JSON string in the database, so the data itself 
can be pretty flexible for each element type. 

The public-facing code in the element is designed to respond to targeted POST and/or GET requests — 
so a CASH Request is made and the element responds to the CASH Response if the correct **element_id** 
was set along with the request. So an element is embedded with a single function call, and will 
respond automatically when interacted with. The whole idea being that it's a simple to use structure 
that can be powerful and flexible enough to innovate on top of the PHP API. 

As a starting point, look at the StaticContent element.
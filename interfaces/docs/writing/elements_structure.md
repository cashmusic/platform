Each element is essentially a bundle all to itself, with three main parts:

  1. An **app.json** definition file that defines element messages and data to be stored
  2. A Class file with logic and states for the element
  3. A folder of mustache templates (markup views for every state)

Data and settings for each instance of an element are stored as encrypted JSON in the database, 
and defined in an element's **app.json** file. The app.json file defines all details like title
and description of the element, instructions, and the data structure along with labels, messages,
etc. A detailed sample file is better than a description here:

<script src="https://gist.github.com/jessevondoom/f471efa218a7ce78fa13.js"></script>

The options define what can be set in the element admin, what options will be present and 
expected, and provide default values, etc. 

The allowed types for options are:
  
  - select
    - values (required)
  - boolean
  - number
  - text
  - markup
  
And every option can also contain:

  - required
  - default
  - displaysize
  - helptext
  - placeholder


#### The main element class
The main element class file extends the ElementBase class, automating most of the state
management and template selection you'll need. The logic can be as complex as necessary,  
but in the end the goal is to define some data into **$this->element_data** and return 
it as the output of the getData() function. Whatever you define will be added to the 
stored data and accessible in your mustache templates. 

In the example below, note that we use the **$this->setTemplate()** function to choose
a template other than the (required) default.mustache file. This is based on filenames in
the template folder, and controlled by state — the CASH Response UID returned the the last
request. So if the element contains a form that triggers a CASH Request via GET or POST
the element responds, sets its internal state, and your getData() function does some magic
based on state before returning the data needed to render your embed.

By separating the getData from rendering output we allow elements to work at all states on
a purely data level — leaving room in the future for mobile app support, etc. But for now
the data is combined with the mustache template you choose to render HTML in the browser. 

<script src="https://gist.github.com/jessevondoom/39eaf1bb6fb84b5c1cd9.js"></script>

This is just a starting point. For more examples see the **/framework/classes/elements**
directory in the main platform repo.
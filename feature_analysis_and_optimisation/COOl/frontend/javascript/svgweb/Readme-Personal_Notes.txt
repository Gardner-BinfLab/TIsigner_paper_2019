For some mysterious reason, this script will fail, if the Javascript nearby calls on innerHTML. E.g.

document.getElementById("Some_ID").innerHTML = <some_content>;

It does not necessarily have to affect the innerHTML of the SVG or the nested objects. This script will fail, even if the target "Some_ID" are somewhere else on the page entirely!
using System;
using System.Data;
using System.Configuration;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Web.UI.HtmlControls;

namespace gov.va.medora.mdws.dto
{
    public class AbstractTaggedArrayTO : AbstractArrayTO
    {
        public string tag;

        public AbstractTaggedArrayTO() { }
    }
}

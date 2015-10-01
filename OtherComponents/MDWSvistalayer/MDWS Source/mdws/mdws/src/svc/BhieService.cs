using System;
using System.Web;
using System.Web.Services;
using System.Web.Services.Protocols;
using System.ComponentModel;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    /// <summary>
    /// Summary description for BhieService
    /// </summary>
    [WebService(Namespace = "http://mdws.medora.va.gov/BhieService")]
    [WebServiceBinding(ConformsTo = WsiProfiles.BasicProfile1_1)]
    [ToolboxItem(false)]
    // To allow this Web Service to be called from script, using ASP.NET AJAX, uncomment the following line. 
    // [System.Web.Script.Services.ScriptService]
    public class BhieService : BaseService
    {
        [WebMethod(EnableSession = true, Description = "Get notes with text from all VHA sites.")]
        public TaggedNoteArrays getNotes(
            string pwd,
            string userSitecode,
            string userName,
            string DUZ,
            string ICN,
            string fromDate,
            string toDate,
            string nNotes)
        {
            //pwd = "iBnOfs55iEZ,d";
            //ICN = "1014270856";
            //fromDate = "20100101";
            //toDate = "20110101";
            return (TaggedNoteArrays)MySession.execute("NoteLib", "getNotesForBhie", new object[] { pwd, ICN, fromDate, toDate, nNotes });
        }

        [WebMethod(EnableSession = true, Description = "Get the patient's VHA sites, data sources and identifiers.")]
        public SiteArray getPatientSites(string pwd, string userSitecode, string userName, string DUZ, string ICN)
        {
            return null;
        }
    }
}

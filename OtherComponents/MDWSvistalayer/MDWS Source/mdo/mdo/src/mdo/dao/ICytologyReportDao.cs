using System;
using System.Collections.Generic;
using System.Linq;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdo.src.mdo.dao.vista
{
    public interface ICytologyReportsDao
    {
        CytologyReport[] getCytologyReports(string fromDate, string toDate, int nrpts);
        CytologyReport[] getCytologyReports(string pid, string fromDate, string toDate, int nrpts);
        //string getLrDfn(string pid);
    }
}

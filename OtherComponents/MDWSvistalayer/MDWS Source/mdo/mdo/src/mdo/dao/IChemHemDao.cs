using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface IChemHemDao
    {
        ChemHemReport[] getChemHemReports(string dfn, string fromDate, string toDate);
        ChemHemReport[] getChemHemReports(string fromDate, string toDate);
        ChemHemReport getChemHemReport(string dfn, ref string nextDate);
        //ChemHemReport[] getChemHemReports(string fromDate, string toDate, int maxRpts);
        Dictionary<string, HashSet<string>> getNewChemHemReports(DateTime start);
    }
}

using System;
using System.Collections.Generic;
using System.Collections.Specialized;

namespace gov.va.medora.mdo.dao.vista.fhie
{
    public class FhieChemHemDao : IChemHemDao
    {
        AbstractConnection cxn;

        VistaChemHemDao vistaDao = null;

        public FhieChemHemDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
            vistaDao = new VistaChemHemDao(cxn);
        }

        public ChemHemReport[] getChemHemReports(string fromDate, string toDate)
        {
            return getChemHemReports(cxn.Pid, fromDate, toDate);
        }

        public ChemHemReport[] getChemHemReports(string dfn, string fromDate, string toDate)
        {
            return vistaDao.getChemHemReports(dfn, fromDate, toDate, 1000);
        }

        public ChemHemReport getChemHemReport(string dfn, ref string nextDate)
        {
            throw new NotImplementedException();
        }


        public Dictionary<string, HashSet<string>> getNewChemHemReports(DateTime start)
        {
            throw new NotImplementedException();
        }
    }
}

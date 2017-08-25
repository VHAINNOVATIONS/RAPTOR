using System;
using System.Collections.Generic;
using System.Collections.Specialized;

namespace gov.va.medora.mdo.dao.vista.fhie
{
    public class FhieLabsDao : ILabsDao
    {
        VistaLabsDao vistaDao = null;
       
        public FhieLabsDao(AbstractConnection cxn)
        {
            vistaDao = new VistaLabsDao(cxn);
        }

        public CytologyReport[] getCytologyReports(string fromDate, string toDate, int nrpts)
        {
            return vistaDao.getCytologyReports(fromDate, toDate, nrpts);
        }

        public CytologyReport[] getCytologyReports(string pid, string fromDate, string toDate, int nrpts)
        {
            return vistaDao.getCytologyReports(pid, fromDate, toDate, nrpts);
        }

        public SurgicalPathologyReport[] getSurgicalPathologyReports(string fromDate, string toDate, int nrpts)
        {
            return vistaDao.getSurgicalPathologyReports(fromDate, toDate, nrpts);
        }

        public SurgicalPathologyReport[] getSurgicalPathologyReports(string pid, string fromDate, string toDate, int nrpts)
        {
            return vistaDao.getSurgicalPathologyReports(pid, fromDate, toDate, nrpts);
        }

        public MicrobiologyReport[] getMicrobiologyReports(string fromDate, string toDate, int nrpts)
        {
            return vistaDao.getMicrobiologyReports(fromDate, toDate, nrpts);
        }

        public MicrobiologyReport[] getMicrobiologyReports(string pid, string fromDate, string toDate, int nrpts)
        {
            return vistaDao.getMicrobiologyReports(pid, fromDate, toDate, nrpts);
        }

        public string getBloodAvailabilityReport(string fromDate, string toDate, int nrpts)
        {
            return null;
        }

        public string getBloodAvailabilityReport(string pid, string fromDate, string toDate, int nrpts)
        {
            return null;
        }

        public string getBloodTransfusionReport(string fromDate, string toDate, int nrpts)
        {
            return null;
        }

        public string getBloodTransfusionReport(string pid, string fromDate, string toDate, int nrpts)
        {
            return null;
        }

        public string getBloodBankReport()
        {
            return null;
        }

        public string getBloodBankReport(string pid)
        {
            return null;
        }

        public string getElectronMicroscopyReport(string fromDate, string toDate, int nrpts)
        {
            return null;
        }

        public string getElectronMicroscopyReport(string pid, string fromDate, string toDate, int nrpts)
        {
            return null;
        }

        public string getCytopathologyReport()
        {
            return null;
        }

        public string getCytopathologyReport(string pid)
        {
            return null;
        }

        public string getAutopsyReport()
        {
            return null;
        }

        public string getAutopsyReport(string pid)
        {
            return null;
        }

        public string getLrDfn(string pid)
        {
            return null;
        }

        public string getAllLabReports(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getAllLabReports(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }


        public IList<LabTest> getTests(string target)
        {
            throw new NotImplementedException();
        }

        public string getTestDescription(string identifierString)
        {
            throw new NotImplementedException();
        }
    }
}

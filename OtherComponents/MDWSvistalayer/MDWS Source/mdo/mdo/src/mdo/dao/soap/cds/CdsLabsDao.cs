using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.cds;

namespace gov.va.medora.mdo.dao.soap.cds
{
    public class CdsLabsDao : ILabsDao
    {
        CdsConnection _cxn;

        public CdsLabsDao(CdsConnection cxn)
        {
            _cxn = cxn;
        }

        public string getAllLabReports(string fromDate, string toDate, int nrpts)
        {
            return getAllLabReports(_cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getAllLabReports(string pid, string fromDate, string toDate, int nrpts)
        {
            string labFilter = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" +
                "<filter:filter vhimVersion=\"Vhim_4_00\"" +
                "	xsi:schemaLocation=\"Filter Lab_Single_Patient_All_Data_Filter.xsd\"" +
                "	xmlns:filter=\"Filter\"" +
                "	xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">" +
                "	<filterId>LAB_SINGLE_PATIENT_ALL_DATA_FILTER</filterId>" +
                "	<patients>" +
                "		<NationalId>" + pid + "</NationalId>" +
                "	</patients>" +
                "	<entryPointFilter queryName=\"ID_1\">" +
                "		<domainEntryPoint>LabTestPromise</domainEntryPoint>" +
                "	</entryPointFilter>" +
                "</filter:filter>";
            string result = _cxn.Proxy.readClinicalData1("MHVLabRead40011", labFilter, "LAB_SINGLE_PATIENT_ALL_DATA_FILTER", "MHV-REQUEST-ID-" + Guid.NewGuid().ToString());
            return result;
        }

        #region NotImplementedMembers

        public SurgicalPathologyReport[] getSurgicalPathologyReports(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public SurgicalPathologyReport[] getSurgicalPathologyReports(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public MicrobiologyReport[] getMicrobiologyReports(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public MicrobiologyReport[] getMicrobiologyReports(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getBloodAvailabilityReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getBloodAvailabilityReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getBloodTransfusionReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getBloodTransfusionReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getBloodBankReport()
        {
            throw new NotImplementedException();
        }

        public string getBloodBankReport(string pid)
        {
            throw new NotImplementedException();
        }

        public string getElectronMicroscopyReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getElectronMicroscopyReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getCytopathologyReport()
        {
            throw new NotImplementedException();
        }

        public string getCytopathologyReport(string pid)
        {
            throw new NotImplementedException();
        }

        public string getAutopsyReport()
        {
            throw new NotImplementedException();
        }

        public string getAutopsyReport(string pid)
        {
            throw new NotImplementedException();
        }

        public string getLrDfn(string pid)
        {
            throw new NotImplementedException();
        }
        #endregion



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

using System;
using System.Collections.Specialized;
using System.Text;
using System.Collections.Generic;

namespace gov.va.medora.mdo.dao
{
    public interface ILabsDao
    {
       // CytologyReport[] getCytologyReports(string fromDate, string toDate, int nrpts);
       // CytologyReport[] getCytologyReports(string pid, string fromDate, string toDate, int nrpts);
        string getAllLabReports(string fromDate, string toDate, int nrpts);
        string getAllLabReports(string pid, string fromDate, string toDate, int nrpts);
        SurgicalPathologyReport[] getSurgicalPathologyReports(string fromDate, string toDate, int nrpts);
        SurgicalPathologyReport[] getSurgicalPathologyReports(string pid, string fromDate, string toDate, int nrpts);
        MicrobiologyReport[] getMicrobiologyReports(string fromDate, string toDate, int nrpts);
        MicrobiologyReport[] getMicrobiologyReports(string pid, string fromDate, string toDate, int nrpts);
        string getBloodAvailabilityReport(string fromDate, string toDate, int nrpts);
        string getBloodAvailabilityReport(string pid, string fromDate, string toDate, int nrpts);
        string getBloodTransfusionReport(string fromDate, string toDate, int nrpts);
        string getBloodTransfusionReport(string pid, string fromDate, string toDate, int nrpts);
        string getBloodBankReport();
        string getBloodBankReport(string pid);
        string getElectronMicroscopyReport(string fromDate, string toDate, int nrpts);
        string getElectronMicroscopyReport(string pid, string fromDate, string toDate, int nrpts);
        string getCytopathologyReport();
        string getCytopathologyReport(string pid);
        string getAutopsyReport();
        string getAutopsyReport(string pid);
        string getLrDfn(string pid);
        IList<LabTest> getTests(string target);
        string getTestDescription(string identifierString);
    }
}

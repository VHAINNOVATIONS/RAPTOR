using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface IRadiologyDao
    {
        Order discontinueOrder(String patientId, string orderIen, string providerDuz, String locationIen, string reasonIen);
        Order discontinueAndSignOrder(String patientId, string orderIen, string providerDuz, String locationIen, string reasonIen, String eSig);
        void signOrder(String orderIen, String providerDuz, String locationIen, String eSig);

        RadiologyReport[] getRadiologyReports(string fromDate, string toDate, int nrpts);
        RadiologyReport[] getRadiologyReportsBySite(string fromDate, string toDate, string siteCode);
        RadiologyReport getImagingReport(string dfn, string accessionNumber);

        IList<RadiologyCancellationReason> getCancellationReasons();
        Dictionary<String, String> getContrastMedia();
        Dictionary<String, String> getComplications();
        IList<ImagingExam> getExamsByPatient(String patientId);
        void cancelExam(String examIdentifier, String reasonIen, bool cancelAssociatedOrder = true, String holdDescription = null);

        ImagingExam registerExam(
            String orderId,
            String examDateTime,
            String examCategory,
            String hospitalLocation,
            String ward,
            String service,
            String technologistComment);

    }
}

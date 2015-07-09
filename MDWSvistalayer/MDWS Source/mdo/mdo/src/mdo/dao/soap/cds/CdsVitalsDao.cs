using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.soap.cds
{
    public class CdsVitalsDao : IVitalsDao
    {
        CdsConnection _cxn;

        public CdsVitalsDao(AbstractConnection cxn)
        {
            _cxn = (CdsConnection)cxn;
        }

        public String getHthVitals(String icn)
        {
            String request = buildGetHthVitalsRequest(icn);
            string result = _cxn.Proxy.readClinicalData1("VitalsignsRead40010", request, "VITAL_SINGLE_PATIENT_ALL_DATA_FILTER", "MHV-REQUEST-ID-" + Guid.NewGuid().ToString());
            return result;
        }

        internal String buildGetHthVitalsRequest(String icn)
        {
            String filter = "<filter:filter xmlns:filter=\"Filter\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" vhimVersion=\"Vhim_4_00\">" +
              "<filterId>VITAL_SINGLE_PATIENT_ALL_DATA_FILTER</filterId>" +
              "<patients>" +
                "<NationalId>{0}</NationalId>" +
              "</patients>" +
              "<entryPointFilter queryName=\"VitalSignObservationEventQuery\">" +
                "<domainEntryPoint>VitalSignObservationEvent</domainEntryPoint>" +
              "</entryPointFilter>" +
            "</filter:filter>";

            return String.Format(filter, icn);
        }

        public VitalSignSet[] getVitalSigns()
        {
            throw new NotImplementedException();
        }

        public VitalSignSet[] getVitalSigns(string pid)
        {
            throw new NotImplementedException();
        }

        public VitalSignSet[] getVitalSigns(string fromDate, string toDate, int maxRex)
        {
            throw new NotImplementedException();
        }

        public VitalSignSet[] getVitalSigns(string pid, string fromDate, string toDate, int maxRex)
        {
            throw new NotImplementedException();
        }

        public VitalSign[] getLatestVitalSigns()
        {
            throw new NotImplementedException();
        }

        public VitalSign[] getLatestVitalSigns(string pid)
        {
            throw new NotImplementedException();
        }
    }
}

using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data.SqlClient;
using System.Data;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class CdwVitalsDao : IVitalsDao
    {
        CdwConnection _cxn;

        public CdwVitalsDao(AbstractConnection cxn)
        {
            _cxn = (CdwConnection)cxn;
        }

        public VitalSignSet[] getVitalSigns()
        {
            return getVitalSigns(_cxn.Pid);
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
            return getLatestVitalSigns(_cxn.Pid);
        }

        public VitalSign[] getLatestVitalSigns(string pid)
        {
            SqlDataAdapter adapter = buildVitalSignsRequest(pid);
            IDataReader reader = _cxn.query(adapter) as IDataReader;

            return toVitalSigns(reader);
        }

        internal SqlDataAdapter buildVitalSignsRequest(string patientIcn) {

            string queryString = "SELECT * FROM App.Vitals where PatientICN = @patientIcn";

            PatientQueryBuilder queryBuilder = new PatientQueryBuilder();
            SqlDataAdapter adapter = queryBuilder.buildPatientSelectQuery(patientIcn, queryString);

            return adapter;
        }

        internal VitalSign[] toVitalSigns(IDataReader reader)
        {
            List<VitalSign> vitalSigns = new List<VitalSign>();
            try
            {
                while (reader.Read())
                {
                    vitalSigns.Add(buildVitalSign(reader));
                }
            }
            catch (Exception) { }
            finally
            {
                reader.Close();
            }

            return vitalSigns.ToArray<VitalSign>();
        }

        internal VitalSign buildVitalSign(IDataReader reader) {

            SiteId facility = new SiteId()
            {
                Id = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("Sta3n")),
                Name = DbReaderUtil.getValue(reader, reader.GetOrdinal("Location"))
            };

            string id = DbReaderUtil.getInt64Value(reader, reader.GetOrdinal("VitalSignSID"));
            string dateTaken = DbReaderUtil.getDateTimeValue(reader, reader.GetOrdinal("VitalSignTakenDateTime"));
            string vitalType = DbReaderUtil.getValue(reader, reader.GetOrdinal("VitalType"));
            string typeId = DbReaderUtil.getInt32Value(reader, reader.GetOrdinal("VitalTypeSID"));
            ObservationType type = new ObservationType(typeId, VitalSign.VITAL_SIGN, vitalType);
            
            VitalSign vitalSign = new VitalSign()
            {
                Id =  id,
                Facility = facility,
                Type = type,
                Value1 = DbReaderUtil.getValue(reader, reader.GetOrdinal("Result")),
                Timestamp = dateTaken
            };

            return vitalSign;
        }

    }
}

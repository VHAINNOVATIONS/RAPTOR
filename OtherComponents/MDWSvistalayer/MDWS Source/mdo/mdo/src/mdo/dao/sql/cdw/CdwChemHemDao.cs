using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data.SqlClient;
using System.Data;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class CdwChemHemDao : IChemHemDao
    {
        CdwConnection _cxn;

        public CdwChemHemDao(AbstractConnection cxn)
        {
            _cxn = cxn as CdwConnection;
        }

        public Dictionary<string, HashSet<string>> getNewChemHemReports(DateTime start)
        {
            if (DateTime.Now.Subtract(start).TotalDays > 30)
            {
                throw new ArgumentException("Only the last 30 days can be retrieved");
            }
            string commandText = "SELECT DISTINCT chem.Sta3n, patient.PatientICN " +
                "FROM Chem.LabChem AS chem " +
                "RIGHT OUTER JOIN SPatient.SPatient AS patient " +
                "ON chem.PatientSID=patient.PatientSID " +
                "WHERE " +
                "( " +
                "	( " +
                "		chem.LabChemCompleteDateTime>=DATEADD(DAY, -30, GETDATE()) AND  " +
                "		chem.VistaEditDate>=@start " +
                "	) " +
                "	OR " +
                "	( " +
                "		chem.LabChemCompleteDateTime IS NULL AND  " +
                "		chem.VistaEditDate>=@start " +
                "	) " +
                ") " +
                ";";
            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(commandText);

            SqlParameter startParam = new SqlParameter("@start", SqlDbType.DateTime);
            startParam.Value = start;
            adapter.SelectCommand.Parameters.Add(startParam);
            adapter.SelectCommand.CommandTimeout = 600; // allow query to run for up to 10 minutes

            using (_cxn)
            {
                IDataReader reader = (IDataReader)_cxn.query(adapter);

                Dictionary<string, HashSet<string>> results = new Dictionary<string, HashSet<string>>();

                while (reader.Read())
                {
                    if (reader.IsDBNull(1))
                    {
                        continue;
                    }
                    string sitecode = reader.GetInt16(0).ToString();
                    string patientICN = reader.GetString(1);

                    if (!results.ContainsKey(sitecode))
                    {
                        results.Add(sitecode, new HashSet<string>());
                    }
                    if (!results[sitecode].Contains(patientICN))
                    {
                        results[sitecode].Add(patientICN);
                    }
                }
                return results;
            }
        }

        internal SqlDataAdapter buildChemHemRequest(string patientIcn, string fromDate, string toDate)
        {
            string queryString = "SELECT * FROM App.ChemLabs where PatientICN = @patientIcn "+
                "AND LabChemCompleteDateTime >= @fromDate AND LabChemCompleteDateTime <= @toDate";

            PatientQueryBuilder queryBuilder = new PatientQueryBuilder();
            SqlDataAdapter adapter = queryBuilder.buildPatientSelectQuery(patientIcn, queryString);
            SqlParameter startDate = new SqlParameter("@fromDate", System.Data.SqlDbType.Date);
            startDate.Value = fromDate;
            SqlParameter endDate = new SqlParameter("@toDate", System.Data.SqlDbType.Date);
            endDate.Value = toDate;
            adapter.SelectCommand.Parameters.Add(startDate);
            adapter.SelectCommand.Parameters.Add(endDate);

            return adapter;
        }

        internal ChemHemReport[] toChemHemReports(IDataReader reader)
        {
            IList<ChemHemReport> reports = new List<ChemHemReport>();

            try
            {
                while (reader.Read())
                {
                    reports.Add(buildChemHemReport(reader));
                }
            }
            catch (Exception e) 
            {
                System.Console.WriteLine(e.InnerException);
            }
            finally
            {
                reader.Close();
            }

            return reports.ToArray<ChemHemReport>();
        }

        internal ChemHemReport buildChemHemReport(IDataReader reader) {
            
            string patientIcn = DbReaderUtil.getValue(reader, reader.GetOrdinal("PatientICN"));
            string testName = DbReaderUtil.getValue(reader, reader.GetOrdinal("LabChemTestName")); // LabResult.LabTest.ShortName
            string resultValue = DbReaderUtil.getValue(reader, reader.GetOrdinal("LabChemResultValue")); // LabResult.Value
            string refHigh = DbReaderUtil.getValue(reader, reader.GetOrdinal("RefHigh")); // LabResult.LabTest.hiRef
            string refLow = DbReaderUtil.getValue(reader, reader.GetOrdinal("RefLow")); // LabResult.LabTest.lowRef
            string abnormal = DbReaderUtil.getValue(reader, reader.GetOrdinal("Abnormal")); // LabResult.BoundaryStatus
            string loinc = DbReaderUtil.getValue(reader, reader.GetOrdinal("LOINC")); // LabResult.LabTest.Loinc
            string component = DbReaderUtil.getValue(reader, reader.GetOrdinal("Component")); // LabResult.LabTest.Name
            string labTestType = DbReaderUtil.getValue(reader, reader.GetOrdinal("LabTestType")); //LabResult.LabTest.Category
            string labChemTestSid = DbReaderUtil.getInt32Value(reader, reader.GetOrdinal("LabChemTestSID"));
            string labChemTestIen = DbReaderUtil.getValue(reader, reader.GetOrdinal("LabChemTestIEN"));
            string labChemIen = DbReaderUtil.getValue(reader, reader.GetOrdinal("LabChemIEN")); // LabTest.Id
            string labChemSid = DbReaderUtil.getInt64Value(reader, reader.GetOrdinal("LabChemSID"));
            string siteCode = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("Sta3n"));
            string labChemCompleteDate = DbReaderUtil.getDateValue(reader, reader.GetOrdinal("LabChemCompleteDateTime"));

            SiteId facility = new SiteId()
            {
                Id = siteCode
            };

            LabTest labTest = new LabTest()
            {
                Id = labChemTestIen,
                Name = testName,
                ShortName = labTestType,
                DataId = labChemTestSid,
                HiRef = refHigh,
                LowRef = refLow,
                Loinc = loinc
            };

            LabResult labResult = new LabResult()
            {
                Value = resultValue,
                BoundaryStatus = abnormal,
                Test = labTest
            };

            LabResult[] labResults = new LabResult[1];
            labResults[0] = labResult;

            LabSpecimen labSpecimen = new LabSpecimen()
            {
                Id = labChemIen,
                Name = DbReaderUtil.getValue(reader, reader.GetOrdinal("Specimen")),
                CollectionDate = labChemCompleteDate,
                Site = siteCode
            };

            ChemHemReport report = new ChemHemReport()
            {
                Id = labChemSid,
                Specimen = labSpecimen,
                Facility = facility,
                Results = labResults,
                Timestamp = labChemCompleteDate
            };

            return report;
        }

        public ChemHemReport[] getChemHemReports(string fromDate, string toDate)
        {
            SqlDataAdapter adapter = buildChemHemRequest(_cxn.Pid, fromDate, toDate);
            IDataReader reader = (IDataReader) _cxn.query(adapter);
            return toChemHemReports(reader);
        }

        #region Not implemented members
        public ChemHemReport getChemHemReport(string dfn, ref string nextDate)
        {
            throw new NotImplementedException();
        }
        #endregion

        public ChemHemReport[] getChemHemReports(string dfn, string fromDate, string toDate)
        {
            throw new NotImplementedException();
        }
    }
}

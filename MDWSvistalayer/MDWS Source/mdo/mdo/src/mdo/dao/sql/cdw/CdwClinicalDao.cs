using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data.SqlClient;
using System.Data;
using System.Collections;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class CdwClinicalDao : IClinicalDao
    {
        CdwConnection _cxn;

        public CdwClinicalDao(AbstractConnection cxn)
        {
            _cxn = (CdwConnection)cxn;
        }
		
		#region Problems

		internal SqlDataAdapter buildProblemListRequest(string patientIcn)
        {
            string queryString = "SELECT * FROM App.ProblemList WHERE PatientICN = @patientIcn";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(queryString);
            SqlParameter patientIdParameter = new SqlParameter("@patientIcn", System.Data.SqlDbType.VarChar, 50);
            patientIdParameter.Value = patientIcn;
            adapter.SelectCommand.Parameters.Add(patientIdParameter);

            return adapter;
        }

        public Problem[] getProblemList(string type)
        {
            SqlDataAdapter adapter = buildProblemListRequest(_cxn.Pid);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toProblemList(reader);
        }

        internal Problem[] toProblemList(IDataReader reader)
        {
            IList<Problem> problemResults = new List<Problem>();
            try
            {
                while (reader.Read())
                {
                    string sta3n = DbReaderUtil.getValue(reader, reader.GetOrdinal("Facility"));
                    SiteId siteId = new SiteId(sta3n);
                    string status = DbReaderUtil.getValue(reader, reader.GetOrdinal("ActiveFlag"));
                    string enteredDate = DbReaderUtil.getDateValue(reader, reader.GetOrdinal("EnteredDate"));
                    string onsetDate = DbReaderUtil.getDateValue(reader, reader.GetOrdinal("OnsetDate"));
                    string modifiedDate = DbReaderUtil.getDateValue(reader, reader.GetOrdinal("LastModifiedDate"));
                    string resolvedDate = DbReaderUtil.getDateValue(reader, reader.GetOrdinal("ResolvedDate"));
                    string priority = DbReaderUtil.getValue(reader, reader.GetOrdinal("Priority"));
                    string icd = DbReaderUtil.getValue(reader, reader.GetOrdinal("ICDDescription"));
                    string icdCode = DbReaderUtil.getValue(reader, reader.GetOrdinal("ICDCode"));
                    string providerNarrative = DbReaderUtil.getValue(reader, reader.GetOrdinal("ProviderNarrative"));
                    string id = DbReaderUtil.getInt64Value(reader, reader.GetOrdinal("ProblemListSID"));
                    string majorDiagnosticCategory = DbReaderUtil.getValue(reader, reader.GetOrdinal("MDC"));

                    Problem problem = new Problem()
                    {
                        Facility = siteId,
                        Status = status,
                        OnsetDate = enteredDate,
                        ModifiedDate = modifiedDate,
                        ResolvedDate = resolvedDate,
                        Priority = priority,
                        NoteNarrative = icd,
                        Id = id,
                        ProviderNarrative = providerNarrative
                    };

                    problemResults.Add(problem);
                }
            }
            catch (Exception)
            { }
            finally
            {
                reader.Close();
            }

            return problemResults.ToArray<Problem>();
        }

        #endregion

        public Hashtable getPatientRecord(string pid, string types)
        {
            Patient patient = new Patient();
            IList<Allergy> allergies = new List<Allergy>();
            IList<Appointment> appointments = new List<Appointment>();
            IList<Note> notes = new List<Note>();
            IList<LabReport> labs = new List<LabReport>();
            IList<Medication> meds = new List<Medication>();
            IList<PathologyReport> pathReports = new List<PathologyReport>();
            IList<Problem> problems = new List<Problem>();
            IList<RadiologyReport> radiologyReports = new List<RadiologyReport>();
            IList<SurgeryReport> surgeryReports = new List<SurgeryReport>();
            IList<Visit> visits = new List<Visit>();
            IList<VitalSignSet> vitals = new List<VitalSignSet>();
            IList<Consult> consults = new List<Consult>();
            IList<HealthSummary> healthSummaries = new List<HealthSummary>();
            IList<PatientRecordFlag> flags = new List<PatientRecordFlag>();
            IList<Immunization> immunizations = new List<Immunization>();
            //Immunizations immunizations = new Immunizations() { Immunization = new List<StructuredProductType>() };

            string[] typesAry = StringUtils.split(types, StringUtils.COMMA);

            foreach (string type in typesAry)
            {
                switch (type)
                {
                    case "demographics":
                        break;
                    case "reactions":
                        break;
                    case "problems":
                        break;
                    case "vitals":
                        break;
                    case "labs":
                        break;
                    case "meds":
                        meds = (IList<Medication>)(new CdwPharmacyDao(_cxn).getAllMeds(pid).ToList());
                        break;
                    case "immunizations":
                        immunizations = (IList<Immunization>)getImmunizations(pid, "", "");
                        break;
                    case "appointments":
                        break;
                    case "visits":
                        break;
                    case "documents":
                        break;
                    case "procedures":
                        // TODO - need to implement
                        break;
                    case "consults":
                        break;
                    case "flags":
                        break;
                    case "healthFactors":
                        break;
                }
            }

            Hashtable results = new Hashtable();
            results.Add("demographics", patient);
            results.Add("reactions", allergies);
            results.Add("healthFactors", healthSummaries);
            results.Add("flags", flags);
            results.Add("consults", consults);
            results.Add("procedures", null);
            results.Add("documents", notes);
            results.Add("visits", visits);
            results.Add("appointments", appointments);
            results.Add("problems", problems);
            results.Add("vitals", vitals);
            results.Add("labs", labs);
            results.Add("meds", meds);
            results.Add("immunizations", immunizations);

            return results;
        }

        #region Immunizations

        internal IList<Immunization> getImmunizations(string icn, string startDate, string stopDate)
        {
            SqlDataAdapter adapter = buildGetImmunizationsQuery(icn, startDate, stopDate);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toImmunizations(reader);
        }

        internal IList<Immunization> toImmunizations(IDataReader reader)
        {
            IList<Immunization> immunizations = new List<Immunization>();

            while (reader.Read())
            {
                //Int32 immSid = (string)DbReaderUtil.getInt32Value(reader, reader.GetOrdinal("ImmunizationSID"));
                string immIen = reader.GetString(reader.GetOrdinal("ImmunizationIEN"));
                Int16 sitecode = reader.GetInt16(reader.GetOrdinal("Sta3n"));
                string patientIen = DbReaderUtil.getValue(reader, reader.GetOrdinal("PatientIEN"));
                //string patientSid = getValue(reader, reader.GetOrdinal("PatientSID"));
                string immunizationName = DbReaderUtil.getValue(reader, reader.GetOrdinal("ImmunizationName"));
                string immunizationShortName = DbReaderUtil.getValue(reader, reader.GetOrdinal("ImmunizationShortName"));
                string inactive = DbReaderUtil.getValue(reader, reader.GetOrdinal("ImmunizationInactiveFlag"));
                string series = DbReaderUtil.getValue(reader, reader.GetOrdinal("Series"));
                string reaction = DbReaderUtil.getValue(reader, reader.GetOrdinal("Reaction"));
                string contraindicated = DbReaderUtil.getValue(reader, reader.GetOrdinal("ContraindicatedFlag"));
                DateTime eventDateTime = reader.IsDBNull(reader.GetOrdinal("EventDateTime")) ? new DateTime() : reader.GetDateTime(reader.GetOrdinal("EventDateTime"));
                DateTime visitDateTime = reader.IsDBNull(reader.GetOrdinal("VisitDateTime")) ? new DateTime() : reader.GetDateTime(reader.GetOrdinal("VisitDateTime"));
                DateTime immunizationDateTime = reader.IsDBNull(reader.GetOrdinal("ImmunizationDateTime")) ? new DateTime() : reader.GetDateTime(reader.GetOrdinal("ImmunizationDateTime"));
                string orderedBy = DbReaderUtil.getValue(reader, reader.GetOrdinal("OrderingStaffIEN"));
                string comments = DbReaderUtil.getValue(reader, reader.GetOrdinal("Comments"));

                Immunization imm = new Immunization()
                {
                    AdministeredDate = eventDateTime.ToString(),
                    Comments = comments,
                    Contraindicated = contraindicated,
                    Encounter = new Visit() { Timestamp = visitDateTime.ToString(), Facility = new SiteId(sitecode.ToString()) },
                    Id = immIen,
                    Name = immunizationName,
                    OrderedBy = new User() { Uid = orderedBy },
                    Reaction = reaction,
                    Series = series,
                    ShortName = immunizationShortName
                };
            }

            return immunizations;
        }

        internal SqlDataAdapter buildGetImmunizationsQuery(string icn, string startDate, string stopDate)
        {
            string sql = "";

            if (!String.IsNullOrEmpty(startDate) && !String.IsNullOrEmpty(stopDate))
            {
                sql = "SELECT IMM.ImmunizationSID, IMM.ImmunizationIEN, IMM.Sta3n, IMM.PatientIEN, IMM.PatientSID, IMM.ImmunizationName, IMM.ImmunizationShortName, " +
                    "IMM.ImmunizationInactiveFlag, IMM.Series, IMM.Reaction, IMM.ContraindicatedFlag, IMM.EventDateTime, IMM.VisitVistaDate, IMM.VisitDateTime, " +
                    "IMM.ImmunizationDateTime, IMM.OrderingStaffIEN, IMM.Comments FROM Immun.Immunization IMM JOIN SPatient.SPatient PAT ON " +
                    "IMM.PatientSID=PAT.PatientSID WHERE PAT.PatientICN=@icn AND IMM.ImmunizationDateTime>@startDate AND IMM.ImmunizationDateTime<@stopDate;";
            }
            else
            {
                sql = "SELECT IMM.ImmunizationSID, IMM.ImmunizationIEN, IMM.Sta3n, IMM.PatientIEN, IMM.PatientSID, IMM.ImmunizationName, IMM.ImmunizationShortName, " +
                    "IMM.ImmunizationInactiveFlag, IMM.Series, IMM.Reaction, IMM.ContraindicatedFlag, IMM.EventDateTime, IMM.VisitVistaDate, IMM.VisitDateTime, " +
                    "IMM.ImmunizationDateTime, IMM.OrderingStaffIEN, IMM.Comments FROM Immun.Immunization IMM JOIN SPatient.SPatient PAT ON " +
                    "IMM.PatientSID=PAT.PatientSID WHERE PAT.PatientICN=@icn;";
            }


            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(sql);

            SqlParameter patientIdParam = new SqlParameter("@icn", System.Data.SqlDbType.VarChar, 50);
            patientIdParam.Value = icn;
            adapter.SelectCommand.Parameters.Add(patientIdParam);

            if (!String.IsNullOrEmpty(startDate) && !String.IsNullOrEmpty(stopDate))
            {
                SqlParameter startDateParam = new SqlParameter("@startDate", System.Data.SqlDbType.SmallDateTime);
                startDateParam.Value = startDate;
                adapter.SelectCommand.Parameters.Add(startDateParam);

                SqlParameter stopDateParam = new SqlParameter("@stopDate", System.Data.SqlDbType.SmallDateTime);
                stopDateParam.Value = stopDate;
                adapter.SelectCommand.Parameters.Add(stopDateParam);
            }

            adapter.SelectCommand.CommandTimeout = 600; // allow query to run for up to 10 minutes
            return adapter;

        }

        #endregion

        #region Allergies

        public Allergy[] getAllergies()
        {
            throw new NotImplementedException();
        }

        public Allergy[] getAllergiesBySite(string siteCode)
        {
            return getAllergiesBySite(_cxn.Pid, siteCode);
        }

        internal Allergy[] getAllergiesBySite(string dfn, string siteCode)
        {
            SqlDataAdapter adapter = buildAllergiesRequest(dfn, siteCode);
            IDataReader reader = (IDataReader)_cxn.query(adapter);

            return toAllergies(reader);
        }

        internal SqlDataAdapter buildAllergiesRequest(string dfn, string siteCode) 
        {
            string query =
                "SELECT A.station__no, " + 
                    "A.station__no+'-'+CONVERT(varchar(20), A.row_id) as id, " + 
                    "A.patient, A.reactant, A.allergy_type, CONVERT(varchar(14), A.verification_datetime_numeric) as dt, " +
                    "C.name as reaction, " +
                    "D.facility " + 
                "FROM CDWWork.Allergies.patient_allergies_120_8 A " +
                    "LEFT OUTER JOIN Allergies.patient_allex_reactions_120_81 as B " +
                        "ON A.station__no = B.station__no and A.row_id = B.patient_allergies " +
                    "LEFT OUTER JOIN Dim.signsymptoms_120_83 as C " +
                        "ON B.station__no = C.station__no and B.reaction = C.row_id " +
                    "LEFT OUTER JOIN Dim.VistaSite as D " +
                        "ON A.station__no = D.sta3n " +
                "WHERE A.station__no = @station AND A.patient = @dfn";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(query);

            SqlParameter stationParameter = new SqlParameter("@station", System.Data.SqlDbType.VarChar);
            stationParameter.Value = siteCode;
            adapter.SelectCommand.Parameters.Add(stationParameter);

            SqlParameter patientIdParameter = new SqlParameter("@dfn", System.Data.SqlDbType.Decimal);
            patientIdParameter.Value = dfn;
            adapter.SelectCommand.Parameters.Add(patientIdParameter);

            return adapter;
        }

        internal Allergy[] toAllergies(IDataReader reader)
        {
            IList<Allergy> allergies = new List<Allergy>();

            try
            {
                while (reader.Read())
                {
                    allergies.Add(toAllergy(reader));
                }
            }
            finally
            {
                reader.Close();
            }

            return allergies.ToArray();
        }

        internal Allergy toAllergy(IDataReader reader)
        {
            string facilityId = DbReaderUtil.getValue(reader, reader.GetOrdinal("station__no"));
            string id = DbReaderUtil.getValue(reader, reader.GetOrdinal("id"));
            string name = DbReaderUtil.getValue(reader, reader.GetOrdinal("reactant"));
            string timestamp = DbReaderUtil.getValue(reader, reader.GetOrdinal("dt"));
            string type = DbReaderUtil.getValue(reader, reader.GetOrdinal("allergy_type"));
            string reaction = DbReaderUtil.getValue(reader, reader.GetOrdinal("reaction"));
            string facilityName = DbReaderUtil.getValue(reader, reader.GetOrdinal("facility"));
            
            SiteId facility = new SiteId()
            {
                Id = facilityId,
                Name = facilityName
            };

            Symptom symptom = new Symptom() 
            {
                Name = reaction,
                Facility = facility,
                Timestamp = timestamp
            };

            List<Symptom> symptoms = new List<Symptom>() { symptom };

            Allergy allergy = new Allergy()
            {
                AllergenId = id,
                AllergenName = name,
                Timestamp = timestamp,
                AllergenType = type,
                Facility = facility, 
                Reactions = symptoms
            };

            return allergy;
        }

        #endregion
		
        #region Surgery

        public SurgeryReport[] getSurgeryReportsBySite(string site)
        {
            SqlDataAdapter adapter = buildSurgeryReportRequest(_cxn.Pid, site);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toSurgeryReports(reader);
        }

        public SurgeryReport[] getSurgeryReports(bool fWithText)
        {
            throw new NotImplementedException();
        }

        internal SqlDataAdapter buildSurgeryReportRequest(string dfn, string station)
        {
            String queryString = 
                "SELECT station__no, patient, station__no+'-'+CONVERT(varchar(20), row_id) as id, CONVERT(varchar(14), date_of_operation_numeric) as dt, principal_procedure "+
                "FROM Surgery.surgery_130 "+
                "WHERE station__no = @station AND patient = @patientId";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(queryString);

            SqlParameter patientIdParameter = new SqlParameter("@patientId", System.Data.SqlDbType.Decimal);
            patientIdParameter.Value = dfn;
            adapter.SelectCommand.Parameters.Add(patientIdParameter);

            SqlParameter stationParameter = new SqlParameter("@station", System.Data.SqlDbType.VarChar);
            stationParameter.Value = station;
            adapter.SelectCommand.Parameters.Add(stationParameter);

            return adapter;
        }

        internal SurgeryReport[] toSurgeryReports(IDataReader reader)
        {
            IList<SurgeryReport> reports = new List<SurgeryReport>();

            while (reader.Read())
            {
                reports.Add(toSurgeryReport(reader));
            }

            return reports.ToArray<SurgeryReport>();
        }

        internal SurgeryReport toSurgeryReport(IDataReader reader)
        {

            SiteId facility = new SiteId()
            {
                Id = DbReaderUtil.getValue(reader, reader.GetOrdinal("station__no"))
            };

            SurgeryReport report = new SurgeryReport()
            {
                Id = DbReaderUtil.getValue(reader, reader.GetOrdinal("id")),
                Title = DbReaderUtil.getValue(reader, reader.GetOrdinal("principal_procedure")),
                Timestamp = DbReaderUtil.getValue(reader, reader.GetOrdinal("dt")),
                Facility = facility
            };

            return report;
        }

        public string getSurgeryReportText(string rptId)
        {
            throw new NotImplementedException();
        }
		#endregion
		
        #region Mental Health

        #region Mental Health Administration
        public List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatient()
        {
            return getMentalHealthInstrumentsForPatient(_cxn.Pid);
        }

        public List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatientBySurvey(string surveyName)
        {
            return getMentalHealthInstrumentsForPatientBySurvey(_cxn.Pid, surveyName);
        }

        public List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatient(string patientIcn)
        {
            String queryString = "SELECT * FROM App.MHSurveyAdministration WHERE PatientICN = @patientIcn";

            SqlDataAdapter adapter = buildMentalHealthInstrumentsForPatientRequest(patientIcn, null, queryString);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toMentalHealthInstrumentAdministrations(reader);
        }

        public List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatientBySurvey(string patientIcn, string surveyName)
        {
            String queryString = "SELECT * FROM App.MHSurveyAdministration WHERE PatientICN = @patientIcn AND SurveyName = @surveyName";

            SqlDataAdapter adapter = buildMentalHealthInstrumentsForPatientRequest(_cxn.Pid, surveyName, queryString);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toMentalHealthInstrumentAdministrations(reader);
        }

        internal SqlDataAdapter buildMentalHealthInstrumentsForPatientRequest(string patientIcn, string surveyName, string queryString)
        {
            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(queryString);
            SqlParameter patientIdParameter = new SqlParameter("@patientIcn", System.Data.SqlDbType.VarChar, 50);
            patientIdParameter.Value = patientIcn;
            adapter.SelectCommand.Parameters.Add(patientIdParameter);

            if (surveyName != null)
            {
                SqlParameter surveyNameParameter = new SqlParameter("@surveyName", System.Data.SqlDbType.VarChar);
                surveyNameParameter.Value = surveyName;
                adapter.SelectCommand.Parameters.Add(surveyNameParameter);
            }

            return adapter;
        }

        internal List<MentalHealthInstrumentAdministration> toMentalHealthInstrumentAdministrations(IDataReader reader) 
        {
            List<MentalHealthInstrumentAdministration> instrumentAdministrations = new List<MentalHealthInstrumentAdministration>();
            try
            {
                while (reader.Read())
                {
                    instrumentAdministrations.Add(toMentalHealthInstrumentAdministration(reader));
                }
            } catch (Exception) {
            } finally {
                reader.Close();
            }

            return instrumentAdministrations;
        }

        internal MentalHealthInstrumentAdministration toMentalHealthInstrumentAdministration(IDataReader reader)
        {
            MentalHealthInstrumentAdministration instrumentAdministration = new MentalHealthInstrumentAdministration()
            {
                Id = DbReaderUtil.getInt32Value(reader, reader.GetOrdinal("SurveyAdministrationSID")),
                Patient = new KeyValuePair<string,string>("", DbReaderUtil.getValue(reader, reader.GetOrdinal("PatientICN"))),
                Instrument = new KeyValuePair<string,string>("", DbReaderUtil.getValue(reader, reader.GetOrdinal("SurveyName"))),
                DateAdministered = DbReaderUtil.getDateValue(reader, reader.GetOrdinal("SurveyGivenDateTime")),
                DateSaved = DbReaderUtil.getDateValue(reader, reader.GetOrdinal("SurveySavedDateTime")),
                OrderedBy = new KeyValuePair<string,string>("", DbReaderUtil.getInt32Value(reader, reader.GetOrdinal("OrderedBySID"))),
                AdministeredBy = new KeyValuePair<string,string>("", DbReaderUtil.getInt32Value(reader, reader.GetOrdinal("AdministeredBySID"))),
                IsSigned = DbReaderUtil.getBooleanFromYNValue(reader, reader.GetOrdinal("IsSignedFlag")),
                IsComplete = DbReaderUtil.getBooleanFromYNValue(reader, reader.GetOrdinal("IsCompleteFlag")),
                NumberOfQuestionsAnswered = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("NumberOfQuestionsAnswered")),
                TransmissionStatus = DbReaderUtil.getValue(reader, reader.GetOrdinal("TransmissionStatus")),
                TransmissionTime = DbReaderUtil.getDateValue(reader, reader.GetOrdinal("TransmisionTime")),
                HospitalLocation = new KeyValuePair<string,string>("", DbReaderUtil.getValue(reader, reader.GetOrdinal("Facility")))
            };

            return instrumentAdministration;
        }
        #endregion

        #region Mental Health Results

        public MentalHealthInstrumentResultSet getMentalHealthInstrumentResultSet(string administrationId)
        {
            return getMentalHealthInstrumentResultSet(_cxn.Pid, administrationId);
        }

        public List<MentalHealthInstrumentResultSet> getMentalHealthInstrumentResultSetsBySurvey(string surveyName)
        {
            return getMentalHealthInstrumentsResultSetRequestBySurvey(_cxn.Pid, surveyName);
        }

        internal List<MentalHealthInstrumentResultSet> getMentalHealthInstrumentsResultSetRequestBySurvey(string patientIcn, string surveyName)
        {
            String queryString = "SELECT * FROM App.MHSurveyResults WHERE PatientICN = @patientIcn AND SurveyName = @surveyName";

            SqlDataAdapter adapter = buildMentalHealthInstrumentsResultSetRequest(_cxn.Pid, null, surveyName, queryString);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toMentalHealthInstrumentResultSets(reader);
        }

        internal MentalHealthInstrumentResultSet getMentalHealthInstrumentResultSet(string patientIcn, string administrationId)
        {
            String queryString = "SELECT * FROM App.MHSurveyResults WHERE PatientICN = @patientIcn AND SurveyAdministrationSID = @administrationId";

            SqlDataAdapter adapter = buildMentalHealthInstrumentsResultSetRequest(_cxn.Pid, administrationId, null, queryString);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            List<MentalHealthInstrumentResultSet> resultSets = toMentalHealthInstrumentResultSets(reader);

            // Using the collection properly closes the reader; by administration id should only 1 or 0 results
            if(resultSets == null || resultSets.Count == 0) {
                return new MentalHealthInstrumentResultSet();
            } 
            else
            {
                return resultSets.ElementAt(0);
            }
        }

        internal SqlDataAdapter buildMentalHealthInstrumentsResultSetRequest(string patientIcn, string administrationId, string surveyName, string queryString) 
        {
            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(queryString);
            SqlParameter patientIdParameter = new SqlParameter("@patientIcn", System.Data.SqlDbType.VarChar, 50);
            patientIdParameter.Value = patientIcn;
            adapter.SelectCommand.Parameters.Add(patientIdParameter);

            if (administrationId != null)
            {
                SqlParameter administrationIdParameter = new SqlParameter("@administrationId", System.Data.SqlDbType.Int);
                administrationIdParameter.Value = Convert.ToInt32(administrationId);
                adapter.SelectCommand.Parameters.Add(administrationIdParameter);
            }
            if (surveyName != null)
            {
                SqlParameter surveyNameParameter = new SqlParameter("@surveyName", System.Data.SqlDbType.VarChar);
                surveyNameParameter.Value = surveyName;
                adapter.SelectCommand.Parameters.Add(surveyNameParameter);
            }

            return adapter;
        }

        internal MentalHealthInstrumentResultSet toMentalHealthInstrumentResultSet(IDataReader reader) 
        {
            MentalHealthInstrumentResultSet result = new MentalHealthInstrumentResultSet()
            {
                Scale = new KeyValuePair<string, string>("Scale", DbReaderUtil.getValue(reader, reader.GetOrdinal("SurveyScale"))),
                RawScore = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("RawScore")),
                Id = DbReaderUtil.getInt32Value(reader, reader.GetOrdinal("SurveyResultSID")),
                Instrument = new KeyValuePair<string, string>("Instrument", DbReaderUtil.getValue(reader, reader.GetOrdinal("SurveyName"))),
                AdministrationId = DbReaderUtil.getInt32Value(reader, reader.GetOrdinal("SurveyAdministrationSID")),
                SurveyGivenDateTime = DbReaderUtil.getDateValue(reader, reader.GetOrdinal("SurveyGivenDateTime")),
                SurveySavedDateTime = DbReaderUtil.getDateValue(reader, reader.GetOrdinal("SurveySavedDateTime"))
            };
            result.TransformedScores.Add("1", DbReaderUtil.getValue(reader, reader.GetOrdinal("TransformedScore1")));
            result.TransformedScores.Add("2", DbReaderUtil.getValue(reader, reader.GetOrdinal("TransformedScore2")));
            result.TransformedScores.Add("3", DbReaderUtil.getValue(reader, reader.GetOrdinal("TransformedScore3")));

            return result;
        }

        internal List<MentalHealthInstrumentResultSet> toMentalHealthInstrumentResultSets(IDataReader reader) {
        
            List<MentalHealthInstrumentResultSet> results = new List<MentalHealthInstrumentResultSet>();

            try {
                while (reader.Read())
                {
                   results.Add(toMentalHealthInstrumentResultSet(reader));
                }
            } catch (Exception)
            { }
            finally
            {
                reader.Close();
            }

            return results;
        }

        public void addMentalHealthInstrumentResultSet(MentalHealthInstrumentAdministration administration)
        {
            throw new NotImplementedException();
        }

        #endregion
        #endregion

        #region Clinic Directory

        public User[] getStaffByCriteria(string siteCode, string searchTerm, string firstName, string lastName, string type)
        {
            SqlDataAdapter adapter = buildClinicDirectoryRequest(type, siteCode, searchTerm, firstName, lastName);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toUsers(reader);
        }

        internal SqlDataAdapter buildClinicDirectoryRequest(string type, string siteCode, string searchTerm, string firstName, string lastName)
        {
            SqlDataAdapter adapter = new SqlDataAdapter();

            string queryString = "SELECT * FROM App.ClinicDirectory WHERE Sta3n = @siteCode";

            if (type.Equals("phone"))
            {
                queryString += " and OfficePhone = @phone OR VoicePager = @phone OR DigitalPager = @phone";
                adapter.SelectCommand = new SqlCommand(queryString);
                SqlParameter phoneParameter = new SqlParameter("@phone", System.Data.SqlDbType.VarChar);
                phoneParameter.Value = searchTerm;
                adapter.SelectCommand.Parameters.Add(phoneParameter);
            }
            else if (type.Equals("email"))
            {
                queryString += " and EmailAddress = @email";
                adapter.SelectCommand = new SqlCommand(queryString);
                SqlParameter emailParameter = new SqlParameter("@email", System.Data.SqlDbType.VarChar, 100);
                emailParameter.Value = searchTerm;
                adapter.SelectCommand.Parameters.Add(emailParameter);
            }
            else
            {
                if (type.Equals("firstAndLast"))
                {
                    queryString += " and LastName = @lastName AND FirstName = @firstName";
                }
                else
                {
                    queryString += " and (LastName = @lastName OR FirstName = @firstName)";
                }
                SqlParameter firstNameParameter = new SqlParameter("@firstName", System.Data.SqlDbType.VarChar, 50);
                firstNameParameter.Value = firstName;
                SqlParameter lastNameParameter = new SqlParameter("@lastName", System.Data.SqlDbType.VarChar, 50);
                lastNameParameter.Value = lastName;
                adapter.SelectCommand = new SqlCommand(queryString);
                adapter.SelectCommand.Parameters.Add(firstNameParameter);
                adapter.SelectCommand.Parameters.Add(lastNameParameter);
            }

            SqlParameter siteCodeParameter = new SqlParameter("@siteCode", System.Data.SqlDbType.VarChar, 3);
            siteCodeParameter.Value = siteCode;
            adapter.SelectCommand.Parameters.Add(siteCodeParameter);

            return adapter;
        }

        internal User[] toUsers(IDataReader reader)
        {
            List<User> users = new List<User>();

            while (reader.Read())
            {
                string id = DbReaderUtil.getInt32Value(reader, reader.GetOrdinal("StaffSID"));
                string sta3n = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("Sta3n"));
                string networkUsername = DbReaderUtil.getValue(reader, reader.GetOrdinal("NetworkUsername"));
                string positionTitle = DbReaderUtil.getValue(reader, reader.GetOrdinal("PositionTitle"));
                string firstName = DbReaderUtil.getValue(reader, reader.GetOrdinal("FirstName"));
                string lastName = DbReaderUtil.getValue(reader, reader.GetOrdinal("LastName"));
                string prefix = DbReaderUtil.getValue(reader, reader.GetOrdinal("StaffNamePrefix"));
                string suffix = DbReaderUtil.getValue(reader, reader.GetOrdinal("StaffNameSuffix"));
                string officePhone = DbReaderUtil.getValue(reader, reader.GetOrdinal("OfficePhone"));
                string phone3 = DbReaderUtil.getValue(reader, reader.GetOrdinal("Phone3"));
                string phone4 = DbReaderUtil.getValue(reader, reader.GetOrdinal("Phone4"));
                string voicePager = DbReaderUtil.getValue(reader, reader.GetOrdinal("VoicePager"));
                string digitalPager = DbReaderUtil.getValue(reader, reader.GetOrdinal("DigitalPager"));
                string emailAddress = DbReaderUtil.getValue(reader, reader.GetOrdinal("EmailAddress"));
                //string signatureBlockTitle = DbReaderUtil.getValue(reader, reader.GetOrdinal("SignatureBlockTitle"));
                //string faxNumber = DbReaderUtil.getValue(reader, reader.GetOrdinal("FaxNumber"));

                SiteId siteId = new SiteId()
                {
                    Id = sta3n
                };

                PersonName personName = new PersonName()
                {
                    Firstname = firstName,
                    Lastname = lastName,
                    Prefix = prefix,
                    Suffix = suffix
                };

                User user = new User()
                {
                    Name = personName,
                    UserName = networkUsername,
                    EmailAddress = emailAddress,
                    VoicePager = voicePager,
                    DigitalPager = digitalPager,
                    Phone = officePhone,
                    Title = positionTitle,
                    LogonSiteId = siteId
                };
                users.Add(user);
            }

            return users.ToArray<User>();
        }

        #endregion

        #region Not Implemented Members

        public string getNhinData(string types = null)
        {
            throw new NotImplementedException();
        }

        public string getNhinData(string pid, string types = null)
        {
            throw new NotImplementedException();
        }

        public string getAllergiesAsXML()
        {
            throw new NotImplementedException();
        }


        public MdoDocument[] getHealthSummaryList()
        {
            throw new NotImplementedException();
        }

        public string getHealthSummaryTitle(string summaryId)
        {
            throw new NotImplementedException();
        }

        public string getHealthSummaryText(string mpiPid, MdoDocument hs, string sourceSiteId)
        {
            throw new NotImplementedException();
        }

        public HealthSummary getHealthSummary(MdoDocument hs)
        {
            throw new NotImplementedException();
        }

        public string getAdHocHealthSummaryByDisplayName(string displayName)
        {
            throw new NotImplementedException();
        }

        #endregion
        
    }
}

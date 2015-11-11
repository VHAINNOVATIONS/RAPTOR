using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data.SqlClient;
using System.Data;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class CdwEncounterDao : IEncounterDao
    {
        CdwConnection _cxn;

        public CdwEncounterDao(AbstractConnection cxn)
        {
            _cxn = (CdwConnection)cxn;
        }

        #region Mental Health Visits

        public Visit[] getMentalHealthVisits()
        {
            return getMentalHealthVisits(_cxn.Pid);
        }

        public Visit[] getMentalHealthVisits(string pid)
        {
            SqlDataAdapter adapter = buildMentalHealthVisitsRequest(pid);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toVisitsFromDataReader(reader);
        }

        internal SqlDataAdapter buildMentalHealthAppointmentRequest(string pid)
        {
            string queryString = "Select * from App.MHAppointments where PatientICN = @patientIcn";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(queryString);

            SqlParameter icnParameter = new SqlParameter("@patientIcn", SqlDbType.VarChar, 50);
            icnParameter.Value = pid;
            adapter.SelectCommand.Parameters.Add(icnParameter);

            return adapter;
        }

        internal SqlDataAdapter buildMentalHealthVisitsRequest(string pid)
        {
            string queryString = "SELECT * FROM App.MHVisits WHERE PatientICN = @patientIcn";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(queryString);

            SqlParameter icnParameter = new SqlParameter("@patientIcn", SqlDbType.VarChar, 50);
            icnParameter.Value = pid;
            adapter.SelectCommand.Parameters.Add(icnParameter);

            return adapter;
        }

        internal Visit[] toVisitsFromDataReader(IDataReader reader)
        {
            IList<Visit> visits = new List<Visit>();
            try
            {
                while (reader.Read())
                {
                    string providerSid = DbReaderUtil.getInt32Value(reader, reader.GetOrdinal("ProviderSID"));
                    //ignore record if we don't have valid provider
                    if (!String.IsNullOrEmpty(providerSid))
                    {
                        string id = DbReaderUtil.getInt64Value(reader, reader.GetOrdinal("VisitSID"));
                        string sta3n = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("Sta3n"));
                        string visitDateTime = DbReaderUtil.getDateTimeValue(reader, reader.GetOrdinal("VisitDateTime"));
                        string locationName = DbReaderUtil.getValue(reader, reader.GetOrdinal("LocationName"));
                        string primaryStopCode = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("PrimaryStopCode"));
                        string secondaryStopCode = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("SecondaryStopCode"));
                        string institutionName = DbReaderUtil.getValue(reader, reader.GetOrdinal("InstitutionName"));
                        string locationType = DbReaderUtil.getValue(reader, reader.GetOrdinal("LocationType"));
                        string medicalService = DbReaderUtil.getValue(reader, reader.GetOrdinal("MedicalService"));
                        string firstName = getStringValue(reader, "FirstName");
                        string lastName = getStringValue(reader, "LastName");
                        string prefix = getStringValue(reader, "StaffNamePrefix");
                        string suffix = getStringValue(reader, "StaffNameSuffix");
                        string providerClass = getStringValue(reader, "ProviderClass");

                        SiteId facility = new SiteId(sta3n, institutionName);

                        HospitalLocation hospitalLocation = new HospitalLocation()
                        {
                            Name = locationName,
                            StopCode = new KeyValuePair<string, string>("Primary", primaryStopCode),
                            Type = locationType
                        };

                        PersonName name = new PersonName()
                        {
                            Firstname = firstName,
                            Lastname = lastName,
                            Prefix = prefix,
                            Suffix = suffix
                        };

                        User provider = new User()
                        {
                            Name = name,
                            ProviderClass = providerClass,
                            UserClass = providerClass
                        };

                        Visit visit = new Visit()
                        {
                            Id = id,
                            Location = hospitalLocation,
                            Timestamp = visitDateTime,
                            Facility = facility,
                            Provider = provider,
                            Service = medicalService
                        };

                        visits.Add(visit);
                    }
                }
            }
            catch (Exception) { }
            finally
            {
                reader.Close();
            }
            return visits.ToArray<Visit>();
        }

        #endregion

        #region Appointments

        public Dictionary<string, HashSet<string>> getUpdatedFutureAppointments(DateTime updatedSince)
        {
            if (DateTime.Now.Subtract(updatedSince).TotalDays > 30)
            {
                throw new ArgumentException("Can not ask for more than 30 days of updates");
            }

            string commandText = "SELECT DISTINCT appt.Sta3n, patient.PatientICN FROM Appt.Appointment as appt " +
                "RIGHT OUTER JOIN SPatient.SPatient as patient " +
                "ON appt.PatientSID=patient.PatientSID " +
                "WHERE AppointmentDateTime>GETDATE() " +
                "AND appt.VistaEditDate>@updatedSince;";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(commandText);

            SqlParameter startParam = new SqlParameter("@updatedSince", SqlDbType.DateTime);
            startParam.Value = updatedSince;
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

        public Appointment[] getMentalHealthAppointments()
        {
            return getMentalHealthAppointments(_cxn.Pid);
        }

        public Appointment[] getMentalHealthAppointments(string pid)
        {
            SqlDataAdapter adapter = buildMentalHealthAppointmentRequest(pid);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toAppointmentsFromDataReader(reader);
        }

        public Appointment[] getAppointments()
        {
            return getAppointments(_cxn.Pid);
        }

        public Appointment[] getAppointments(string pid)
        {
            SqlDataAdapter adapter = buildGetAppointmentsRequest(pid);
            IDataReader rdr = (IDataReader)_cxn.query(adapter);
            return toAppointmentsFromDataReader(rdr);
        }

        internal SqlDataAdapter buildGetAppointmentsRequest(string pid)
        {
            string commandText = "SELECT * from App.Appointments where PatientICN = @patientIcn";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(commandText);

            SqlParameter patientIcnParameter = new SqlParameter("@patientIcn", SqlDbType.VarChar, 50);
            patientIcnParameter.Value = pid;
            adapter.SelectCommand.Parameters.Add(patientIcnParameter);
            
            return adapter;
        }

        internal Appointment[] toAppointmentsFromDataReader(IDataReader reader)
        {
            IList<Appointment> appointments = new List<Appointment>();
            try
            {
                while (reader.Read())
                {
                    string id = DbReaderUtil.getInt64Value(reader, reader.GetOrdinal("AppointmentSID"));
                    string sta3n = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("Sta3n"));
                    string appointmentDateTime = DbReaderUtil.getDateTimeValue(reader, reader.GetOrdinal("AppointmentDateTime"));
                    string purposeOfVisit = DbReaderUtil.getValue(reader, reader.GetOrdinal("PurposeOfVisit"));
                    string locationName = DbReaderUtil.getValue(reader, reader.GetOrdinal("LocationName"));
                    string primaryStopCode = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("PrimaryStopCode"));
                    string secondaryStopCode = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("SecondaryStopCode"));
                    string institutionName = DbReaderUtil.getValue(reader, reader.GetOrdinal("InstitutionName"));
                    string locationType = DbReaderUtil.getValue(reader, reader.GetOrdinal("LocationType"));
                    string medicalService = DbReaderUtil.getValue(reader, reader.GetOrdinal("MedicalService"));
                    string visitId = DbReaderUtil.getInt64Value(reader, reader.GetOrdinal("VisitSID"));
                    string providerName = providerNameIfAvailable(reader);

                    SiteId facility = new SiteId(sta3n, institutionName);
                    HospitalLocation hospitalLocation = new HospitalLocation();
                    hospitalLocation.Name = locationName;
                    hospitalLocation.StopCode = new KeyValuePair<string, string>("Primary", primaryStopCode);
                    hospitalLocation.Type = medicalService;

                    Appointment appointment = new Appointment()
                    {
                        Id = id,
                        VisitId = visitId,
                        Clinic = hospitalLocation,
                        Timestamp = appointmentDateTime,
                        Purpose = purposeOfVisit,
                        Facility = facility,
                        ProviderName = providerName
                    };

                    appointments.Add(appointment);
                }
            }
            catch (Exception) { }
            finally
            {
                reader.Close();
            }
            return appointments.ToArray<Appointment>();
        }

        internal String providerNameIfAvailable(IDataReader reader)
        {
            string providerName = "";
            try
            {
                providerName = DbReaderUtil.getValue(reader, reader.GetOrdinal("StaffName"));
            }
            catch (Exception) { } // ignore

            return providerName;
        }


        public Appointment[] getFutureAppointments()
        {
            throw new NotImplementedException();
        }

        public Appointment[] getFutureAppointments(string pid)
        {
            throw new NotImplementedException();
        }

        public Appointment[] getAppointments(int pastDays, int futureDays)
        {
            throw new NotImplementedException();
        }

        public Appointment[] getAppointments(string pid, int pastDays, int futureDays)
        {
            throw new NotImplementedException();
        }

        public string getAppointmentText(string apptId)
        {
            return getAppointmentText(_cxn.Pid, apptId);
        }

        public string getAppointmentText(string pid, string apptId)
        {
            SqlDataAdapter adapter = buildAppointmentTextRequest(pid, apptId);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toNoteText(reader);
        }

        internal SqlDataAdapter buildAppointmentTextRequest(string patientIcn, string visitSid)
        {
            string commandText = "SELECT * from App.OutpatientNotes where PatientICN = @patientIcn and VisitSID = @visitSid";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(commandText);

            SqlParameter patientIcnParameter = new SqlParameter("@patientIcn", SqlDbType.VarChar, 50);
            patientIcnParameter.Value = patientIcn;
            adapter.SelectCommand.Parameters.Add(patientIcnParameter);

            SqlParameter visitParameter = new SqlParameter("@visitSid", SqlDbType.VarChar, 50);
            visitParameter.Value = visitSid;
            adapter.SelectCommand.Parameters.Add(visitParameter);

            return adapter;
        }

        internal string toNoteText(IDataReader reader)
        {
            string report = "";
            try
            {
                while (reader.Read())
                {
                    string text = DbReaderUtil.getValue(reader, reader.GetOrdinal("ReportText"));
                    string title = DbReaderUtil.getValue(reader, reader.GetOrdinal("TIUStandardTitle"));
                    string date = DbReaderUtil.getDateTimeValue(reader, reader.GetOrdinal("EntryDateTime"));

                    report = concatenateNote(report, text, title, date);
                }
            }
            catch (Exception) { }
            finally
            {
                reader.Close();
            }

            return report;
        }

        # endregion

        #region Patient Care Team

        public PatientCareTeam getPatientCareTeamMembers(string station)
        {
            return getPatientCareTeamMembers(_cxn.Pid, station);
        }

        public PatientCareTeam getPatientCareTeamMembers(string patientIcn, string station)
        {
            SqlDataAdapter adapter = buildPatientCareTeamMembers(patientIcn, station);
            IDataReader reader = (IDataReader)_cxn.query(adapter);
            return toPatientCareTeamFromDataReader(reader);
        }

        internal SqlDataAdapter buildPatientCareTeamMembers(string patientIcn, string station)
        {
            string storedProcedure = "App.CurrentPatientCareTeam";

            SqlDataAdapter adapter = _cxn.createAdapterForStoredProcedure(storedProcedure);
            adapter.SelectCommand.Parameters.Add("PatientICN", SqlDbType.VarChar).Value = patientIcn;

            return adapter;
        }

        internal PatientCareTeam toPatientCareTeamFromDataReader(IDataReader reader)
        {
            PatientCareTeam team = new PatientCareTeam();

            try
            {
                while (reader.Read())
                {
                    PatientCareTeamMember member = new PatientCareTeamMember();

                    string teamName = getStringValue(reader, "Team");
                    string teamStartDate = DbReaderUtil.getDateTimeValue(reader, reader.GetOrdinal("PatientTeamStartDate"));
                    string teamEndDate = DbReaderUtil.getDateTimeValue(reader, reader.GetOrdinal("PatientTeamEndDate"));
                    string currentProviderFlag = getStringValue(reader, "CurrentProviderFlag");
                    string associateProviderFlag = getStringValue(reader, "AssociateProviderFlag");
                    string teamPurpose = getStringValue(reader, "TeamPurpose");
                    string providerRole = getStringValue(reader, "ProviderRole");
                    string primaryPosition = getStringValue(reader, "PrimaryPosition");
                    string primaryStandardPosition = getStringValue(reader, "PrimaryStandardPosition");
                    string associatePosition = getStringValue(reader, "AssociatePosition");
                    string associateStandardPosition = getStringValue(reader, "AssociateStandardPosition");

                    member.TeamName = teamName;
                    member.TeamStartDate = teamStartDate;
                    member.TeamEndDate = teamEndDate;
                    member.CurrentProviderFlag = currentProviderFlag;
                    member.AssociateProviderFlag = associateProviderFlag;
                    member.TeamPurpose = teamPurpose;
                    member.ProviderRole = providerRole;
                    member.PrimaryPosition = primaryPosition;
                    member.PrimaryStandardPosition = primaryStandardPosition;
                    member.AssociatePosition = associatePosition;
                    member.AssociateStandardPosition = associateStandardPosition;


                    
                    Person provider = new Person();
                    PersonName providerName = new PersonName();
                    string staffName = getStringValue(reader, "StaffName");
                    string staffSid = DbReaderUtil.getInt32Value(reader, reader.GetOrdinal("StaffSID")).ToString();
                    string firstName = getStringValue(reader, "FirstName");
                    string lastName = getStringValue(reader, "LastName");
                    string prefix = getStringValue(reader, "StaffNamePrefix");
                    string suffix = getStringValue(reader, "StaffNameSuffix");

                    providerName.Firstname = firstName;
                    providerName.Lastname = lastName;
                    providerName.Prefix = prefix;
                    providerName.Suffix = suffix;

                    provider.Name = providerName;
                    provider.Id = staffSid;
                    member.Person = provider;

                    team.Members.Add(member);
                }
            }
            catch (Exception ex) {
                System.Console.Write(ex.Message);
            }
            finally
            {
                reader.Close();
            }

            return team;
        }

        #endregion

        #region Admissions

        public InpatientStay[] getAdmissions()
        {
            return getAdmissions(_cxn.Pid);
        }

        public InpatientStay[] getAdmissions(string patientIcn)
        {
            SqlDataAdapter adapter = buildAdmissionRequest(patientIcn);
            IDataReader reader = (IDataReader)_cxn.query(adapter);

            return toAdmissions(reader);
        }

        internal SqlDataAdapter buildAdmissionRequest(string patientIcn) {

            string queryString = "SELECT * FROM App.Admissions WHERE PatientICN = @patientIcn";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(queryString);

            SqlParameter patientIdParameter = new SqlParameter("@patientIcn", System.Data.SqlDbType.VarChar);
            patientIdParameter.Value = patientIcn;
            adapter.SelectCommand.Parameters.Add(patientIdParameter);

            return adapter;
        }

        internal InpatientStay[] toAdmissions(IDataReader reader)
        {
            IList<InpatientStay> admissions = new List<InpatientStay>();

            try
            {
                while (reader.Read())
                {
                    admissions.Add(toAdmission(reader));
                }
            }
            finally
            {
                reader.Close();
            }
            
            return admissions.ToArray();
        }

        internal InpatientStay toAdmission(IDataReader reader)
        {
            HospitalLocation location = new HospitalLocation()
            {
                Id = DbReaderUtil.getInt16Value(reader, reader.GetOrdinal("Sta3n")),
                Name = DbReaderUtil.getValue(reader, reader.GetOrdinal("Facility"))

            };

            InpatientStay stay = new InpatientStay()
            {
                AdmitTimestamp = DbReaderUtil.getDateTimeValue(reader, reader.GetOrdinal("AdmitDateTime")),
                DischargeTimestamp = DbReaderUtil.getDateTimeValue(reader, reader.GetOrdinal("DischargeDateTime")),
                Location = location,
                MovementCheckinId = DbReaderUtil.getInt64Value(reader, reader.GetOrdinal("InpatientSID"))
            };

            return stay;
        }

        #endregion

        #region Discharge Notes

        public string getDischargesReport(string fromDate, string toDate, int nrpts)
        {
            return getDischargesReport(_cxn.Pid, fromDate, toDate, nrpts);
        }

        internal SqlDataAdapter buildDischargeNoteRequest(string patientIcn, string fromDate, string toDate)
        {
            string queryString = "SELECT * FROM App.InpatientNotes WHERE PatientICN = @patientIcn AND EntryDateTime > @fromDate AND EntryDateTime < @toDate ORDER BY EntryDateTime";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(queryString);

            SqlParameter patientIdParameter = new SqlParameter("@patientIcn", System.Data.SqlDbType.VarChar);
            patientIdParameter.Value = patientIcn;
            adapter.SelectCommand.Parameters.Add(patientIdParameter);

            SqlParameter fromDateParameter = new SqlParameter("@fromDate", System.Data.SqlDbType.Date);
            fromDateParameter.Value = fromDate;
            adapter.SelectCommand.Parameters.Add(fromDateParameter);

            SqlParameter toDateParameter = new SqlParameter("@toDate", System.Data.SqlDbType.Date);
            toDateParameter.Value = toDate;
            adapter.SelectCommand.Parameters.Add(toDateParameter);

            return adapter;
        }

        internal string toDischargeNote(IDataReader reader)
        {
            string dischargeNote = "";

            try
            {
                while (reader.Read())
                {
                    string text = DbReaderUtil.getValue(reader, reader.GetOrdinal("ReportText"));
                    string title = DbReaderUtil.getValue(reader, reader.GetOrdinal("TIUStandardTitle"));
                    string date = DbReaderUtil.getDateTimeValue(reader, reader.GetOrdinal("EntryDateTime"));

                    dischargeNote = concatenateNote(dischargeNote, text, title, date);
                }
            }
            finally
            {
                reader.Close();
            }

            return dischargeNote;
        }

        internal string concatenateNote(string existingNote, string reportText, string title, string date)
        {
            string mergedNote = existingNote;
            mergedNote += "\n ----- ";
            if (!String.IsNullOrEmpty(title))
            {
                mergedNote += title;
            }
            mergedNote = mergedNote + " (" + date + ")";
            mergedNote += " -----\n\n";
            mergedNote += reportText;

            return mergedNote;
        }

        public string getDischargesReport(string patientIcn, string fromDate, string toDate, int nrpts)
        {
            SqlDataAdapter adapter = buildDischargeNoteRequest(patientIcn, fromDate, toDate);
            IDataReader reader = (IDataReader)_cxn.query(adapter);

            return toDischargeNote(reader);
        }

        public string getDischargeDiagnosisReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getDischargeDiagnosisReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        #endregion

        private string getStringValue(IDataReader reader, string column)
        {
            return DbReaderUtil.getValue(reader, reader.GetOrdinal(column));
        }

        #region Not implemented members


        public Adt[] getInpatientMoves(string fromDate, string toDate)
        {
            throw new NotImplementedException();
        }

        public Adt[] getInpatientMovesByCheckinId(string checkinId)
        {
            throw new NotImplementedException();
        }

        public Adt[] getInpatientMoves()
        {
            throw new NotImplementedException();
        }

        public Adt[] getInpatientMoves(string pid)
        {
            throw new NotImplementedException();
        }

        public Adt[] getInpatientMoves(string fromDate, string toDate, string iterLength)
        {
            throw new NotImplementedException();
        }

        public HospitalLocation[] lookupLocations(string target, string direction)
        {
            throw new NotImplementedException();
        }

        public System.Collections.Specialized.StringDictionary lookupHospitalLocations(string target)
        {
            throw new NotImplementedException();
        }

        public string getLocationId(string locationName)
        {
            throw new NotImplementedException();
        }

        public HospitalLocation[] getWards()
        {
            throw new NotImplementedException();
        }

        public HospitalLocation[] getClinics(string target, string direction)
        {
            throw new NotImplementedException();
        }

        public InpatientStay[] getStaysForWard(string wardId)
        {
            throw new NotImplementedException();
        }

        public Drg[] getDRGRecords()
        {
            throw new NotImplementedException();
        }

        public Visit[] getOutpatientVisits()
        {
            throw new NotImplementedException();
        }

        public Visit[] getOutpatientVisits(string pid)
        {
            throw new NotImplementedException();
        }

        public Visit[] getVisits(string fromDate, string toDate)
        {
            throw new NotImplementedException();
        }

        public Visit[] getVisits(string pid, string fromDate, string toDate)
        {
            throw new NotImplementedException();
        }

        public Visit[] getVisitsForDay(string theDate)
        {
            throw new NotImplementedException();
        }

        public string getServiceConnectedCategory(string initialCategory, string locationIen, bool outpatient)
        {
            throw new NotImplementedException();
        }

        public string getOutpatientEncounterReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getOutpatientEncounterReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getAdmissionsReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getAdmissionsReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getExpandedAdtReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getExpandedAdtReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getTransfersReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getTransfersReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getFutureClinicVisitsReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getFutureClinicVisitsReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getPastClinicVisitsReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getPastClinicVisitsReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getTreatingSpecialtyReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getTreatingSpecialtyReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getCareTeamReport()
        {
            throw new NotImplementedException();
        }

        public string getCareTeamReport(string pid)
        {
            throw new NotImplementedException();
        }

        public IcdReport[] getIcdProceduresReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public IcdReport[] getIcdProceduresReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public IcdReport[] getIcdSurgeryReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public IcdReport[] getIcdSurgeryReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getCompAndPenReport(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getCompAndPenReport(string pid, string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public DictionaryHashList getSpecialties()
        {
            throw new NotImplementedException();
        }

        public DictionaryHashList getTeams()
        {
            throw new NotImplementedException();
        }

        public Adt[] getInpatientDischarges(string pid)
        {
            throw new NotImplementedException();
        }

        public InpatientStay[] getStayMovementsByDateRange(string fromDate, string toDate)
        {
            throw new NotImplementedException();
        }

        public InpatientStay getStayMovements(string checkinId)
        {
            throw new NotImplementedException();
        }

        public Site[] getSiteDivisions(string siteId)
        {
            throw new NotImplementedException();
        }

        public IList<Appointment> getPendingAppointments(string startDate)
        {
            throw new NotImplementedException();
        }

        public string getClinicAvailability(string clinicId)
        {
            throw new NotImplementedException();
        }

        public IList<AppointmentType> getAppointmentTypes(string target)
        {
            throw new NotImplementedException();
        }

        public Appointment makeAppointment(Appointment appointment)
        {
            throw new NotImplementedException();
        }

        public Appointment cancelAppointment(Appointment appointment, string cancellationReason, string remarks)
        {
            throw new NotImplementedException();
        }

        public Appointment checkInAppointment(Appointment appointment)
        {
            throw new NotImplementedException();
        }


        public HospitalLocation getClinicSchedulingDetails(string clinicId)
        {
            throw new NotImplementedException();
        }
        #endregion



        
    }
}

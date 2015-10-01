using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaSchedulingDao : ISchedulingDao
    {
        AbstractConnection cxn;

        public VistaSchedulingDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        #region Scheduling Code

        #region Helpers
        /// <summary>
        /// Helper method for parsing scheduling responses that adhere to format for boolean type responses
        /// </summary>
        /// <param name="response"></param>
        /// <returns>bool</returns>
        internal bool parseBoolResponse(string response)
        {
            if (String.IsNullOrEmpty(response))
            {
                return false;
            }
            if (response.StartsWith("1"))
            {
                return true;
            }
            // TBD - correct exception handling??
            string[] pieces = StringUtils.split(response, StringUtils.EQUALS);
            if (pieces.Length >= 2)
            {
                string[] errorPieces = StringUtils.split(pieces[1], StringUtils.CARET);
                if (errorPieces.Length >= 2)
                {
                    throw new MdoException(String.Concat(errorPieces[0], " - ", errorPieces[1]));
                }
            }
            return false;
        }
        #endregion

        #region EWL

        public IList<EwlItem> getEwl(string pid, string status, string startDate, string stopDate)
        {
            MdoQuery request = buildGetEwlQuery(pid, status, startDate, stopDate);
            string response = (string)cxn.query(request);
            return toEwl(response);
        }

        internal MdoQuery buildGetEwlQuery(string pid, string status, string startDate, string stopDate)
        {
            VistaQuery vq = new VistaQuery("");
            vq.addParameter(vq.LITERAL, pid);
            vq.addParameter(vq.LITERAL, status);
            vq.addParameter(vq.LITERAL, startDate);
            vq.addParameter(vq.LITERAL, stopDate);
            return vq;
        }

        internal IList<EwlItem> toEwl(string response)
        {
            throw new NotImplementedException();
        }

        #endregion

        #region Clinic Details

        public HospitalLocation getClinicSchedulingDetails(String clinicId, String startDateTime)
        {
            DdrGetsEntry request = buildGetClinicSchedulingDetailsQuery(clinicId);
            string[] response = request.execute();
            HospitalLocation result = toClinicSchedulingDetails(response);
            
            Int32 clinicDisplayStartTimeInt = 0;
            Int32 apptLengthInt = 0;
            if (String.IsNullOrEmpty(result.ClinicDisplayStartTime) || !Int32.TryParse(result.ClinicDisplayStartTime, out clinicDisplayStartTimeInt) ||
                String.IsNullOrEmpty(result.AppointmentLength) || !Int32.TryParse(result.AppointmentLength, out apptLengthInt))
            {
                throw new mdo.exceptions.DataException("The clinic has not been configured correctly in VistA");
            }
            
            result.Availability = getClinicAvailability(clinicId, startDateTime, clinicDisplayStartTimeInt, apptLengthInt); // supplement availability

            return result;
        }

        internal DdrGetsEntry buildGetClinicSchedulingDetailsQuery(string clinicId)
        {
            DdrGetsEntry query = new DdrGetsEntry(this.cxn);
            query.File = "44";
            query.Fields = ".01;1;2;7;9;24;1912;1914;1917";
            query.Flags = "IE";
            query.Iens = clinicId + ",";
            return query;
        }

        internal HospitalLocation toClinicSchedulingDetails(string[] response)
        {
            HospitalLocation result = new HospitalLocation();

            if (response == null || response.Length <= 0 || String.IsNullOrEmpty(response[0]) || response[0].Contains("ERROR"))
            {
                throw new MdoException("Invalid response for building clinic scheduling details");
            }

            foreach (string line in response)
            {
                if (string.IsNullOrEmpty(line))
                {
                    continue;
                }

                if (!(line.StartsWith("44")))
                {
                    continue;
                }

                string[] pieces = StringUtils.split(line, StringUtils.CARET);

                if (pieces == null || pieces.Length != 5)
                {
                    continue;
                }

                switch (pieces[2])
                {
                    //query.Fields = ".01;1;2;7;9;24;1912;1914;1917";
                    case ".01":
                        result.Name = pieces[3];
                        break;
                    case "1":
                        result.Abbr = pieces[3];
                        break;
                    case "2":
                        result.Type = pieces[3];
                        result.TypeExtension = new KeyValuePair<string, string>(result.Type, pieces[4]);
                        break;
                    case "7":
                        result.VisitLocation = pieces[3];
                        break;
                    case "9":
                        result.Service = new KeyValuePair<string, string>(pieces[3], pieces[4]);
                        break;
                    case "24":
                        result.AskForCheckIn = String.Equals(pieces[3], "1") | String.Equals(pieces[3], "Y", StringComparison.CurrentCultureIgnoreCase);
                        break;
                    case "1912":
                        result.AppointmentLength = pieces[3];
                        break;
                    case "1914":
                        result.ClinicDisplayStartTime = pieces[3];
                        break;
                    case "1917":
                        result.DisplayIncrements = pieces[3];
                        break;
                }
            }

            return result;
        }

        #endregion

        #region Appointment Type

        public IList<AppointmentType> getAppointmentTypes(string start)
        {
            MdoQuery request = null;
            string response = "";

            try
            {
                request = buildGetAppointmentTypesRequest("", start, "");
                response = (string)cxn.query(request, new MenuOption(VistaConstants.SCHEDULING_CONTEXT));
                return toAppointmentTypes(response);
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetAppointmentTypesRequest(string search, string start, string number)
        {
            VistaQuery request = new VistaQuery("SD APPOINTMENT LIST BY NAME");
            request.addParameter(request.LITERAL, search);
            request.addParameter(request.LITERAL, start);
            request.addParameter(request.LITERAL, number);
            return request;
        }

        internal IList<AppointmentType> toAppointmentTypes(string response)
        {
            if (String.IsNullOrEmpty(response))
            {
                return new List<AppointmentType>();
            }

            IList<AppointmentType> appts = new List<AppointmentType>();

            string[] lines = StringUtils.split(response, StringUtils.CRLF);

            if (lines == null || lines.Length == 0)
            {
                throw new MdoException(MdoExceptionCode.DATA_UNEXPECTED_FORMAT);
            }

            string[] metaLine = StringUtils.split(lines[0], StringUtils.EQUALS);
            string[] metaPieces = StringUtils.split(metaLine[1], StringUtils.CARET);
            Int32 numResult = Convert.ToInt32(metaPieces[0]);
            // metaPieces[1] = number of records requested (number argument). asterisk means all were returned
            // metaPieces[2] = ?

            for (int i = 1; i < lines.Length; i++)
            {
                string[] pieces = StringUtils.split(lines[i], StringUtils.EQUALS);

                if (pieces.Length < 2 || String.IsNullOrEmpty(pieces[1])) // at the declaration of a new result - create a new appointment type
                {
                    if (lines.Length >= i + 2) // just to be safe - check there are two more lines so we can obtain the ID and name
                    {
                        AppointmentType current = new AppointmentType();
                        current.ID = (StringUtils.split(lines[i + 1], StringUtils.EQUALS))[1];
                        current.Name = (StringUtils.split(lines[i + 2], StringUtils.EQUALS))[1];
                        appts.Add(current);
                    }
                }
            }

            // TBD - should we check the meta info matched the number of results found?
            return appts;
        }

        public AppointmentType getAppointmentTypeDetails(string apptTypeIen)
        {
            MdoQuery request = null;
            string response = "";

            try
            {
                request = buildGetAppointmentTypeDetailsRequest(apptTypeIen);
                response = (string)cxn.query(request);
                return toAppointmentTypeDetails(response);
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetAppointmentTypeDetailsRequest(string apptTypeIen)
        {
            VistaQuery request = new VistaQuery("SD GET APPOINTMENT TYPE");
            request.addParameter(request.LITERAL, apptTypeIen);
            return request;
        }

        /// <summary>
        /// RESULT(\"DEFAULT ELIGIBILITY\")=4^COLLATERAL OF VET.
        //RESULT(\"DESCRIPTION\")=REC(409.1,\"7,\",\"DESCRIPTION\")^REC(409.1,\"7,\",\"DESCRIPTION\")
        //RESULT(\"DUAL ELIGIBILITY ALLOWED\")=1^YES
        //RESULT(\"IGNORE MEANS TEST BILLING\")=1^IGNORE
        //RESULT(\"INACTIVE\")=
        //RESULT(\"NAME\")=COLLATERAL OF VET.^COLLATERAL OF VET.
        //RESULT(\"NUMBER\")=7^7
        //RESULT(\"SYNONYM\")=COV^COV
        /// </summary>
        /// <param name="response"></param>
        /// <returns></returns>
        internal AppointmentType toAppointmentTypeDetails(string response)
        {
            AppointmentType result = new AppointmentType();

            if (String.IsNullOrEmpty(response))
            {
                return result;
            }

            string[] lines = StringUtils.split(response, StringUtils.CRLF);

            if (lines == null || lines.Length == 0)
            {
                return result;
            }

            foreach (string line in lines)
            {
                string[] pieces = StringUtils.split(line, StringUtils.EQUALS);

                if (pieces == null || pieces.Length != 2)
                {
                    continue;
                }

                string fieldLabel = StringUtils.extractQuotedString(pieces[0]);
                string[] dataPieces = StringUtils.split(pieces[1], StringUtils.CARET);

                if (dataPieces == null || dataPieces.Length != 2)
                {
                    continue;
                }

                switch (fieldLabel)
                {
                    case "DEFAULT ELGIBILITY":
                        break;
                    case "DESCRIPTION":
                        result.Description = dataPieces[1];
                        break;
                    case "DUAL ELIGIBILITY ALLOWED":
                        break;
                    case "IGNORE MEANS TEST BILLING":
                        break;
                    case "INACTIVE":
                        // haven't seen this populated anywhere - what value comes across for inactive??
                        //result.Active = (dataPieces[1] == "I"); // this was just a guess - probably "1" or "0"
                        break;
                    case "NAME":
                        result.Name = dataPieces[1];
                        break;
                    case "NUMBER":
                        result.ID = dataPieces[1];
                        break;
                    case "SYNONYM":
                        result.Synonym = dataPieces[1];
                        break;
                }
            }

            return result;
        }
        #endregion

        #region Schedule Appointment
        public Appointment makeAppointment(Appointment appointment)
        {
            // TBD - should we check appt first?
            MdoQuery request = buildMakeAppointmentRequest(appointment);
            string response = (string)cxn.query(request, new MenuOption(VistaConstants.SCHEDULING_CONTEXT));
            toAppointmentCheck(response); // throws exception on error
            return appointment;
        }

        internal MdoQuery buildMakeAppointmentRequest(Appointment appointment)
        {
            VistaQuery request = new VistaQuery("SD APPOINTMENT MAKE");
            request.addParameter(request.LITERAL, cxn.Pid);
            request.addParameter(request.LITERAL, appointment.Clinic.Id);
            request.addParameter(request.LITERAL, appointment.Timestamp);
            request.addParameter(request.LITERAL, appointment.Purpose);
            request.addParameter(request.LITERAL, appointment.PurposeSubcategory);
            request.addParameter(request.LITERAL, appointment.Length);
            request.addParameter(request.LITERAL, appointment.AppointmentType.ID);
            //request.addParameter(request.LITERAL, ""); // seems to throw an error if this argument is not present though documentation says optional
            return request;
        }
        #endregion

        #region Check-In
        public Appointment checkInAppointment(Appointment appointment)
        {
            MdoQuery request = buildCheckInAppointmentRequest(appointment);
            string response = (string)cxn.query(request);
            return toCheckInAppointment(response);
        }

        internal MdoQuery buildCheckInAppointmentRequest(Appointment appointment)
        {
            VistaQuery request = new VistaQuery("SD APPOINTMENT CHECK-IN");
            request.addParameter(request.LITERAL, appointment.Id);
            return request;
        }

        internal Appointment toCheckInAppointment(string response)
        {
            throw new NotImplementedException();
        }
        #endregion

        #region Cancel Appointment
        public Appointment cancelAppointment(Appointment appointment, string cancellationReason, string remarks)
        {
            MdoQuery request = buildCancelAppointmentRequest(appointment, cancellationReason, remarks);
            string response = (string)cxn.query(request);
            return toCanceledAppointment(appointment, response);
        }

        public MdoQuery buildCancelAppointmentRequest(Appointment appointment, string cancellationReason, string remarks)
        {
            VistaQuery request = new VistaQuery("SD APPOINTMENT CANCEL");
            request.addParameter(request.LITERAL, cxn.Pid);
            request.addParameter(request.LITERAL, appointment.Clinic.Id);
            request.addParameter(request.LITERAL, appointment.Timestamp);
            request.addParameter(request.LITERAL, appointment.AppointmentType.ID);
            request.addParameter(request.LITERAL, cancellationReason);
            request.addParameter(request.LITERAL, remarks);
            return request;
        }

        public Appointment toCanceledAppointment(Appointment originalAppt, string response)
        {
            if (String.IsNullOrEmpty(response) || String.Equals(response, "1"))
            {
                originalAppt.Id = originalAppt.Timestamp = ""; // null these out to show something happened
            }
            else if (response.Contains("="))
            {
                string[] pieces = StringUtils.split(response, StringUtils.EQUALS);
                string[] codeAndMessage = StringUtils.split(pieces[1], StringUtils.CARET);
                throw new MdoException("Unable to cancel appointment: " + codeAndMessage[0] + " - " + codeAndMessage[1]);
            }
            return originalAppt;
        }

        public Dictionary<string, string> getCancellationReasons()
        {
            MdoQuery request = buildGetCancellationReasonsRequest();
            string response = (string)cxn.query(request);
            return toCancellationReasons(response);
        }

        internal MdoQuery buildGetCancellationReasonsRequest()
        {
            VistaQuery vq = new VistaQuery("SD LIST CANCELLATION REASONS");
            vq.addParameter(vq.LITERAL, ""); // search
            vq.addParameter(vq.LITERAL, ""); // start
            vq.addParameter(vq.LITERAL, ""); // number
            return vq;
        }

        internal Dictionary<string, string> toCancellationReasons(string response)
        {
            if (String.IsNullOrEmpty(response))
            {
                return new Dictionary<string, string>();
            }

            Dictionary<string, string> results = new Dictionary<string, string>();

            string[] lines = StringUtils.split(response, StringUtils.CRLF);

            if (lines == null || lines.Length == 0)
            {
                throw new MdoException(MdoExceptionCode.DATA_UNEXPECTED_FORMAT);
            }

            string[] metaLine = StringUtils.split(lines[0], StringUtils.EQUALS);
            string[] metaPieces = StringUtils.split(metaLine[1], StringUtils.CARET);
            Int32 numResult = Convert.ToInt32(metaPieces[0]);
            // metaPieces[1] = number of records requested (number argument). asterisk means all were returned
            // metaPieces[2] = ?

            for (int i = 1; i < lines.Length; i++)
            {
                if (String.IsNullOrEmpty(lines[i]))
                {
                    continue;
                }

                string[] pieces = StringUtils.split(lines[i], StringUtils.EQUALS);

                if (pieces.Length < 2 || String.IsNullOrEmpty(pieces[1])) // e.g. RESULT(1)=
                {
                    // next two lines contain id and name
                    string id = StringUtils.split(lines[i + 1], StringUtils.EQUALS)[1];
                    string name = StringUtils.split(lines[i + 2], StringUtils.EQUALS)[1];
                    results.Add(id, name);
                    i++; // loop is going to add one more
                }
            }

            return results;
        }

        #endregion

        #region Stop Codes

        public bool isValidStopCode(string stopCodeId)
        {
            MdoQuery request = buildIsValidStopCodeRequest(stopCodeId);
            string response = (string)cxn.query(request);
            return toIsValidStopCode(response);
        }

        internal MdoQuery buildIsValidStopCodeRequest(string stopCodeId)
        {
            VistaQuery vq = new VistaQuery("SD VALID STOP CODE");
            vq.addParameter(vq.LITERAL, stopCodeId); // clinic IEN
            return vq;
        }

        internal bool toIsValidStopCode(string response)
        {
            return parseBoolResponse(response);
        }

        public bool hasValidStopCode(string clinicId)
        {
            MdoQuery request = buildHasValidStopCodeRequest(clinicId);
            string response = (string)cxn.query(request);
            return toValidStopCode(response);
        }

        internal MdoQuery buildHasValidStopCodeRequest(string clinicId)
        {
            VistaQuery vq = new VistaQuery("SD VALID CLINIC STOP CODE");
            vq.addParameter(vq.LITERAL, clinicId); // clinic IEN
            return vq;
        }

        internal bool toValidStopCode(string response)
        {
            return parseBoolResponse(response);
        }

        #endregion

        #region Check Appointment

        public bool getAppointmentCheck(string clinicIen, string startDate, string apptLength)
        {
            MdoQuery request = null;
            string response = "";

            try
            {
                request = buildGetAppointmentCheckRequest(cxn.Pid, clinicIen, startDate, apptLength);
                response = (string)cxn.query(request, new MenuOption(VistaConstants.SCHEDULING_CONTEXT));
                return toAppointmentCheck(response);
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetAppointmentCheckRequest(string dfn, string clinicIen, string startDate, string apptLength)
        {
            VistaQuery request = new VistaQuery("SD APPOINTMENT CHECK");
            request.addParameter(request.LITERAL, clinicIen);
            request.addParameter(request.LITERAL, dfn);
            request.addParameter(request.LITERAL, startDate);
            request.addParameter(request.LITERAL, apptLength);
            return request;
        }

        // TBD - don't appear to be receiving 1/0 for valid appointments - just an empty string. should that return true???
        internal bool toAppointmentCheck(string response)
        {
            if (String.IsNullOrEmpty(response) || String.Equals("1", response))
            {
                return true;
            }

            if (String.Equals(0, response))
            {
                return false;
            }

            if (response.Contains("="))
            {
                string[] pieces = StringUtils.split(response, StringUtils.EQUALS);
                string[] codeAndMessage = StringUtils.split(pieces[1], StringUtils.CARET);
                throw new MdoException("Invalid appointment: " + codeAndMessage[0] + " - " + codeAndMessage[1]);
            }
            return false;
        }

        #endregion

        #region Get Availability

        internal IList<TimeSlot> getClinicAvailability(string clinicIen, String startDateTime, int clinicStartTime, int apptLength)
        {
            //DdrLister request = null;
            MdoQuery request = null;
            //string[] response = null;
            string response = "";

            try
            {
                //request = buildGetClinicAvailabilityRequestDdr(clinicIen);
                request = buildGetClinicAvailabilityRequest(clinicIen, startDateTime);
                //response = (string[])request.execute();
                response = (string)cxn.query(request, new MenuOption(VistaConstants.SCHEDULING_CONTEXT));
                return toClinicAvailability(response, clinicStartTime, apptLength);
            }
            catch (Exception exc)
            {
                //throw new MdoException(request.buildRequest().buildMessage(), exc);
                throw new MdoException(request, response, exc);
            }
        }

        internal DdrLister buildGetClinicAvailabilityRequestDdr(string clinicIen, String startDateTime)
        {
            DdrLister request = new DdrLister(this.cxn);
            request.File = "44.005"; // PATTERN subfile of the HOSPITAL LOCATION file
            request.Fields = ".01;1;2;3";
            request.Flags = "IP";
            //request.From = "3120722";
            request.Iens = "," + clinicIen + ",";
            request.Max = "30";
            request.Xref = "#";
            return request;
        }

        internal MdoQuery buildGetClinicAvailabilityRequest(string clinicIen, String startDateTime)
        {
            VistaQuery request = new VistaQuery("SD GET CLINIC AVAILABILITY");
            request.addParameter(request.LITERAL, clinicIen);
           // request.addParameter(request.LITERAL, startDateTime);
            return request;
        }

        internal IList<TimeSlot> toClinicAvailability(string response, int clinicStartTime, int apptLength)
        {
            return parseAvailabilitString(response, clinicStartTime, apptLength);
        }

        internal IList<TimeSlot> parseAvailabilitString(string availability, int clinicStartTime, int apptLengthMins)
        {
            string[] lines = availability.Split(new char[] { '\n' });
            IList<TimeSlot> results = new List<TimeSlot>();
            foreach (String line in lines)
            {
                if (String.IsNullOrEmpty(line) || line.Contains("0)=")) // doesn't seem to be any meaninful info...
                {
                    continue;
                }

                string[] pieces = line.Split(new char[] { '=' });
                if (pieces.Length != 2)
                {
                    continue;
                }
                // get current day
                int dateStartIdx = pieces[0].IndexOf('(');
                int dateEndIdx = pieces[0].IndexOf(',');
                string date = pieces[0].Substring(dateStartIdx + 1, dateEndIdx - dateStartIdx - 1);
                DateTime theDay = convertVistaDate(date);

                if (!String.IsNullOrEmpty(pieces[1]) && pieces[1].StartsWith(" ")) // holiday!
                {
                    pieces[1] = pieces[1].Trim();
                    pieces[1] = pieces[1].Substring(2); // substring past 2 day date string
                    string holidayName = pieces[1].Trim();
                    TimeSlot holiday = new TimeSlot()
                    {
                        Available = false,
                        Start = theDay,
                        End = theDay.AddDays(1).Subtract(new TimeSpan(0, 1, 0)),
                        Text = holidayName
                    };
                    results.Add(holiday);
                    continue;
                }

                if (String.IsNullOrEmpty(pieces[1]) || (!pieces[1].Contains("[") && !pieces[1].Contains("|")))
                {
                    continue; // not a valid time slot line
                }
                // get items in day
                int startIdx = 8; // this looks to be a constant width - NOT TRUE!

                int iFlag = 0;
                int slotCount = 0;
                DateTime dtClinicStartTime = new DateTime(theDay.Year, theDay.Month, theDay.Day, Convert.ToInt32(clinicStartTime), 0, 0);
                while (startIdx < pieces[1].Length)
                {
                    TimeSlot current = new TimeSlot()
                    {
                        Start = dtClinicStartTime.AddMinutes(slotCount * apptLengthMins),
                        End = dtClinicStartTime.AddMinutes((slotCount + 1) * apptLengthMins),
                    };

                    //string timeDisplayPrefix = current.start.Hour + ":" + current.start.Minute.ToString("00") + " - ";

                    char flag = pieces[1][startIdx];
                    if ((65 <= flag && flag <= 90) || ((97 <= flag && flag <= 122))) // overbooked if character letter
                    {
                        current.Available = false;
                        current.Text = flag.ToString();
                    }
                    else if (Int32.TryParse(flag.ToString(), out iFlag))
                    {
                        if (iFlag > 0)
                        {
                            current.Available = true;
                            current.Text = iFlag.ToString(); // timeDisplayPrefix + iFlag.ToString() + " Available";
                        }
                        else
                        {
                            current.Available = false;
                            current.Text = "0"; //timeDisplayPrefix + "All Slots Full!";
                        }
                    }
                    else
                    {
                        current.Available = false;
                        current.Text = "No availability"; // timeDisplayPrefix + "Unavailable";
                    }
                    // don't forget to increment slotCount
                    slotCount++;
                    startIdx += getIndexShiftForApptLength(apptLengthMins);
                    results.Add(current);
                }
            }
            return results;
        }

        internal int getIndexShiftForApptLength(int apptLength)
        {
            switch (apptLength)
            {
                case 60:
                    return 8;
                case 30:
                    return 4;
                case 20:
                    return 2; // TBD - is this valid??? need to stage test with 20 min appt length
                case 15:
                    return 2;
                case 10:
                    return 2;
                default:
                    throw new ArgumentException(apptLength.ToString() + " is not a standard appointment length");
            }
        }

        internal DateTime convertVistaDate(string vistaDate)
        {
            int iVistaDate = Convert.ToInt32(Math.Floor(Convert.ToDouble(vistaDate))); //Convert.ToInt32(vistaDate);
            if (iVistaDate < 10000000) // 3121227 vs 20121227
            {
                iVistaDate = iVistaDate + 17000000;
            }
            int year = iVistaDate / 10000;
            int month = (iVistaDate - (year * 10000)) / 100;
            int day = (iVistaDate - (year * 10000)) - month * 100;
            return new DateTime(year, month, day);
        }


        internal IList<Appointment> toClinicAvailabilityDdr(string[] response)
        {
            throw new NotImplementedException();
        }

        #endregion

        #region Pending Appointments

        public IList<Appointment> getPendingAppointments(string startDate)
        {
            return getPendingAppointments(cxn.Pid, startDate);
        }

        internal IList<Appointment> getPendingAppointments(string pid, string startDate)
        {
            MdoQuery request = null;
            string response = "";

            try
            {
                request = buildGetPendingAppointmentsRequest(pid, startDate);
                response = (string)cxn.query(request);
                return toPendingAppointments(response);
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetPendingAppointmentsRequest(string pid, string startDate)
        {
            VistaQuery request = new VistaQuery("SD GET PATIENT PENDING APPTS");
            request.addParameter(request.LITERAL, pid);
            request.addParameter(request.LITERAL, startDate);
            return request;
        }

        internal IList<Appointment> toPendingAppointments(string response)
        {
            IList<Appointment> result = new List<Appointment>();

            if (String.IsNullOrEmpty(response))
            {
                return result;
            }

            string[] lines = StringUtils.split(response, StringUtils.CRLF);

            if (lines == null || lines.Length == 0)
            {
                return result;
            }

            Dictionary<string, Appointment> apptDict = new Dictionary<string, Appointment>();

            foreach (string line in lines)
            {
                int timestampStart = line.IndexOf("(");
                int timestampEnd = line.IndexOf(",", timestampStart + 1);
                if (timestampStart <= 0 || timestampEnd <= 0 || timestampEnd <= timestampStart)
                {
                    continue;
                }
                string timestamp = line.Substring(timestampStart + 1, timestampEnd - timestampStart - 1);

                if (!apptDict.ContainsKey(timestamp))
                {
                    apptDict.Add(timestamp, new Appointment());
                    apptDict[timestamp].Timestamp = timestamp; // set this right away so we don't do it every trip through the loop below
                }

                Appointment current = apptDict[timestamp];

                string[] pieces = StringUtils.split(line, StringUtils.EQUALS);
                if (pieces == null || pieces.Length != 2)
                {
                    continue;
                }

                string fieldLabel = StringUtils.extractQuotedString(pieces[0]);

                switch (fieldLabel)
                {
                    case "APPOINTMENT TYPE":
                        current.AppointmentType = new AppointmentType() { Name = pieces[1] };
                        current.Type = pieces[1];
                        break;
                    case "CLINIC":
                        current.Clinic = new HospitalLocation() { Name = pieces[1] };
                        break;
                    case "COLLATERAL VISIT":
                        current.Purpose = pieces[1];
                        break;
                    case "CONSULT LINK":
                        break;
                    case "EKG DATE/TIME":
                        current.EkgDateTime = pieces[1];
                        break;
                    case "LAB DATE/TIME":
                        current.LabDateTime = pieces[1];
                        break;
                    case "LENGTH OF APP'T":
                        current.Length = pieces[1];
                        break;
                    case "X-RAY DATE/TIME":
                        current.XrayDateTime = pieces[1];
                        break;
                    default:
                        break;
                }
            }

            foreach (Appointment appt in apptDict.Values) // copy appts over to list
            {
                result.Add(appt);
            }
            return result;
        }
        #endregion

        #region Has Pending Appointments

        //public IList<Appointment> getPendingAppointments(string startDate)
        //{
        //    return getPendingAppointments(cxn.Pid, startDate);
        //}

        //internal IList<Appointment> getPendingAppointments(string pid, string startDate)
        //{
        //    MdoQuery request = null;
        //    string response = "";

        //    try
        //    {
        //        request = buildGetPendingAppointmentsRequest(pid, startDate);
        //        response = (string)cxn.query(request);
        //        return toPendingAppointments(response);
        //    }
        //    catch (Exception exc)
        //    {
        //        throw new MdoException(request, response, exc);
        //    }
        //}

        //internal MdoQuery buildGetPendingAppointmentsRequest(string pid, string startDate)
        //{
        //    VistaQuery request = new VistaQuery("SD GET PATIENT PENDING APPTS");
        //    request.addParameter(request.LITERAL, pid);
        //    request.addParameter(request.LITERAL, startDate);
        //    return request;
        //}

        //internal IList<Appointment> toPendingAppointments(string response)
        //{
        //    throw new NotImplementedException();
        //}

        #endregion

        #region Eligibility

        public string getEligibilityDetails(string eligibilityIen)
        {
            MdoQuery request = null;
            string response = "";

            try
            {
                request = buildGetEligibilityRequest(eligibilityIen);
                response = (string)cxn.query(request);
                return toEligibility(response);
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetEligibilityRequest(string eligibilityIen)
        {
            VistaQuery request = new VistaQuery("SD GET ELIGIBILITY DETAILS");
            request.addParameter(request.LITERAL, "EMPLOYEE");
            return request;
        }

        internal string toEligibility(string response)
        {
            throw new NotImplementedException();
        }


        #endregion

        #region Appointment Request Types

        public Dictionary<string, string> getAppointmentRequestTypes()
        {
            MdoQuery request = buildGetAppointmentRequestTypesQuery();
            string response = (string)cxn.query(request);
            return toAppointmentRequestTypes(response);
        }

        internal MdoQuery buildGetAppointmentRequestTypesQuery()
        {
            VistaQuery vq = new VistaQuery("SD GET REQUEST TYPES");
            return vq;
        }

        /// <summary>
        /// RESULT(0)=7
        //RESULT(1)=N^'NEXT AVAILABLE' APPT.
        //RESULT(2)=C^OTHER THAN 'NEXT AVA.' (CLINICIAN REQ.)
        //RESULT(3)=P^OTHER THAN 'NEXT AVA.' (PATIENT REQ.)
        //RESULT(4)=W^WALKIN APPT.
        //RESULT(5)=M^MULTIPLE APPT. BOOKING
        //RESULT(6)=A^AUTO REBOOK
        //RESULT(7)=O^OTHER THAN 'NEXT AVA.' APPT.
        /// </summary>
        /// <param name="response"></param>
        /// <returns></returns>
        internal Dictionary<string, string> toAppointmentRequestTypes(string response)
        {
            Dictionary<string, string> result = new Dictionary<string, string>();
            if (String.IsNullOrEmpty(response))
            {
                return result;
            }

            string[] lines = StringUtils.split(response, StringUtils.CRLF);

            if (lines == null || lines.Length == 0)
            {
                return result;
            }

            foreach (string line in lines)
            {
                string[] pieces = StringUtils.split(line, StringUtils.EQUALS);
                if (pieces == null || pieces.Length != 2)
                {
                    continue;
                }

                string[] dataPieces = StringUtils.split(pieces[1], StringUtils.CARET);
                if (dataPieces == null || dataPieces.Length != 2 || String.IsNullOrEmpty(dataPieces[0]) || String.IsNullOrEmpty(dataPieces[1]))
                {
                    continue;
                }

                result.Add(dataPieces[0], dataPieces[1]);
            }
            return result;
        }

        #endregion

        #region Security and Permissions

        /// <summary>
        /// Verify logged on user has scheduling access to clinic
        /// </summary>
        /// <param name="clinicId">Clinic IEN</param>
        /// <returns>bool</returns>
        public bool hasClinicAccess(string clinicId)
        {
            MdoQuery request = buildHasClinicAccessRequest(clinicId);
            string response = (string)cxn.query(request);
            return toHasClinicAccess(response);
        }

        internal MdoQuery buildHasClinicAccessRequest(string clinicId)
        {
            VistaQuery vq = new VistaQuery("SD VERIFY CLINIC ACCESS");
            vq.addParameter(vq.LITERAL, clinicId); // clinic IEN
            return vq;
        }

        internal bool toHasClinicAccess(string response)
        {
            return parseBoolResponse(response);
        }

        #endregion

        #endregion

    }
}

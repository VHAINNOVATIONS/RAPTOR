#define REFACTORING /// adding ability to set interval length and precision to getInpatientMoves
using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Collections;
using System.Text;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaEncounterDao : IEncounterDao
    {
        AbstractConnection cxn = null;

        public VistaEncounterDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        #region Decodings

        internal string decodeHospitalLocationType(string code)
        {
            if (code == "C")
            {
                return "CLINIC";
            }
            else if (code == "M")
            {
                return "MODULE";
            }
            else if (code == "W")
            {
                return "WARD";
            }
            else if (code == "Z")
            {
                return "OTHER LOCATION";
            }
            else if (code == "N")
            {
                return "NON-CLINIC STOP";
            }
            else if (code == "F")
            {
                return "FILE AREA";
            }
            else if (code == "I")
            {
                return "IMAGING";
            }
            else if (code == "OR")
            {
                return "OPERATING ROOM";
            }
            return "";
        }

        internal string decodeHospitalLocationDispositionAction(string code)
        {
            if (code == "0")
            {
                return "NONE";
            }
            else if (code == "1")
            {
                return "APPOINTMENT REC REQ";
            }
            else if (code == "2")
            {
                return "SCHEDULING";
            }
            return "";
        }

        internal string decodeApptPurpose(string code)
        {
            if (code == "1")
            {
                return "C&P";
            }
            if (code == "2")
            {
                return "10-10";
            }
            if (code == "3")
            {
                return "SCHEDULED VISIT";
            }
            if (code == "4")
            {
                return "UNSCHED. VISIT";
            }
            return code;
        }

        internal string decodeApptStatus(string status)
        {
            if (status == "N")
            {
                return "NO-SHOW";
            }
            if (status == "C")
            {
                return "CANCELLED BY CLINIC";
            }
            if (status == "NA")
            {
                return "NO-SHOW & AUTO RE-BOOK";
            }
            if (status == "CA")
            {
                return "CANCELLED BY CLINIC & AUTO RE-BOOK";
            }
            if (status == "I")
            {
                return "INPATIENT APPOINTMENT";
            }
            if (status == "PC")
            {
                return "CANCELLED BY PATIENT";
            }
            if (status == "PCA")
            {
                return "CANCELLED BY PATIENT & AUTO-REBOOK";
            }
            if (status == "NT")
            {
                return "NO ACTION TAKEN";
            }
            return status;
        }

        internal string decodePatientMovementTransaction(string code)
        {
            if (code == "1")
            {
                return "ADMISSION";
            }
            if (code == "2")
            {
                return "TRANSFER";
            }
            if (code == "3")
            {
                return "DISCHARGE";
            }
            if (code == "4")
            {
                return "CHECK-IN LODGER";
            }
            if (code == "5")
            {
                return "CHECK-OUT LODGER";
            }
            if (code == "6")
            {
                return "SPECIALTY TRANSFER";
            }
            return "";
        }

        internal string decodeHospitalLocationService(string code)
        {
            if (code == "M")
            {
                return "MEDICINE";
            }
            else if (code == "S")
            {
                return "SURGERY";
            }
            else if (code == "P")
            {
                return "PSYCHIATRY";
            }
            else if (code == "R")
            {
                return "REHAB MEDICINE";
            }
            else if (code == "N")
            {
                return "NEUROLOGY";
            }
            else if (code == "0")
            {
                return "NONE";
            }
            return "";
        }

        #endregion

        #region Appointments

        public Appointment[] getMentalHealthAppointments()
        {
            throw new NotImplementedException();
        }

        public Appointment[] getAppointments(int pastDays, int futureDays)
        {
            return getAppointments(cxn.Pid, pastDays, futureDays);
        }

        public Appointment[] getAppointments(string dfn, int pastDays, int futureDays)
        {
            int[] defaultRange = getDefaultApptDateRange();
            if (defaultRange[0] != pastDays || defaultRange[1] != futureDays)
            {
                setApptDateRange(pastDays, futureDays);
            }
            MdoQuery request = buildGetAppointmentsRequest(dfn);
            string response = (string)cxn.query(request);
            if (defaultRange[0] != pastDays || defaultRange[1] != futureDays)
            {
                setApptDateRange(defaultRange[0], defaultRange[1]);
            }
            return toAppointments(response);
        }

        internal MdoQuery buildGetAppointmentsRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            VistaQuery vq = new VistaQuery("ORWCV VST");
            vq.addParameter(vq.LITERAL,dfn);
            return vq;
        }

        internal Appointment[] toAppointments(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            ArrayList lst = new ArrayList();
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i] == "")
                {
                    continue;
                }
                Appointment appointment = new Appointment();
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                appointment.Facility = cxn.DataSource.SiteId;
                appointment.Id = flds[0];
                appointment.Timestamp = flds[1];
                appointment.Title = flds[2];
                if (flds.Length > 3)
                {
                    appointment.Status = flds[3];
                }
                lst.Add(appointment);
            }
            return (Appointment[])lst.ToArray(typeof(Appointment));
        }

        internal int[] getDefaultApptDateRange()
        {
            MdoQuery request = buildGetDefaultApptDateRange();
            string response = (string)cxn.query(request);
            return toDefaultApptDateRange(response);
        }

        internal MdoQuery buildGetDefaultApptDateRange()
        {
            VistaQuery vq = new VistaQuery("ORWTPD1 GETCSRNG");
            return vq;
        }

        internal int[] toDefaultApptDateRange(string response)
        {
            int[] result = new int[2];
            string[] flds = response.Split(new char[] { '^' });
            for (int i = 0; i < 2; i++)
            {
                result[i] = Convert.ToInt16(flds[i].Substring(2));
            }
            return result;
        }

        internal void setApptDateRange(int pastDays, int futureDays)
        {
            MdoQuery request = buildSetApptDateRange(pastDays, futureDays);
            string response = (string)cxn.query(request);
        }

        internal MdoQuery buildSetApptDateRange(int pastDays, int futureDays)
        {
            string param = "30^120^-" + pastDays.ToString() + "^" + futureDays.ToString() + "^";
            VistaQuery vq = new VistaQuery("ORWTPD1 PUTCSRNG");
            vq.addParameter(vq.LITERAL, param);
            return vq;
        }

        public string getAppointmentText(string appointmentIen)
        {
            return getAppointmentText(cxn.Pid, appointmentIen);
        }

        public string getAppointmentText(string dfn, string appointmentIen)
        {
            MdoQuery request = buildGetAppointmentTextRequest(dfn, appointmentIen);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetAppointmentTextRequest(string dfn, string appointmentIen)
        {
            VistaUtils.CheckRpcParams(dfn);
            VistaQuery vq = new VistaQuery("ORWCV DTLVST");
            vq.addParameter(vq.LITERAL, dfn);
            vq.addParameter(vq.LITERAL, "0");
            vq.addParameter(vq.LITERAL, appointmentIen);
            return vq;
        }

        public Appointment[] getAppointments()
        {
            return getAppointments(cxn.Pid);
        }

        public Appointment[] getAppointments(string dfn)
        {
            return getAppointmentsByDdr(dfn);
        }

        public Appointment[] getAppointmentsByDdr()
        {
            return getAppointmentsByDdr(cxn.Pid);
        }

        public Appointment[] getAppointmentsByDdr(string dfn)
        {
            DdrLister query = buildGetAppointmentsByDdrQuery(dfn);
            string[] response = query.execute();
            return toAppointmentsFromDdr(response);
        }

        // Ticket 2752 - also see VistaEncounterX.testTicket2752
        //internal DdrLister buildGetAppointmentsByDdrQueryPre2752(string dfn)
        //{
        //    VistaUtils.CheckRpcParams(dfn);
        //    DdrLister query = new DdrLister(cxn);
        //    query.File = "2.98";

        //    //Took out .01E because it blows up the query when it's a zombie pointer
        //    //We'll get the Hospital Location name conditionally with Id.
        //    //Leaving 9.5E in there since it points to APPOINTMENT TYPE which seems
        //    //like it should be stable.
        //    string sFlds = ".001;.01;3;5;6;7;9;9.5";
        //    query.Fields = sFlds;
        //    query.Flags = "IP";
        //    query.Iens = "," + dfn + ",";
        //    query.Xref = "#";

        //    //NB: the calculation for current status (CURRENT^SDAMU) HAS to happen
        //    //AFTER setting HL
        //    //query.Id = "S D0=DA(1),D1=DA," +
        //    //            "HL=$P(^(0),U,1) " +
        //    //            "D CURRENT^SDAMU D EN^DDIOL(X) " +
        //    //            "I $D(^SC(HL,99))=1 " +
        //    //            "S PH=$P($G(^SC(HL,99)),U,1)," +
        //    //            "DV=$P($G(^SC(HL,0)),U,15) " +
        //    //            "S NM=$P($G(^DG(40.8,DV,0)),U,1) " +
        //    //            "S LN=$$GET1^DIQ(44,HL_\",\",.01) " +
        //    //            "D EN^DDIOL($G(PH)_\"|\"_NM_\"|\"_LN)";
        //    query.Id = "S D0=DA(1),D1=DA,HL=$P(^(0),U,1) " +
        //               "D CURRENT^SDAMU D EN^DDIOL(X) " +
        //               "I $D(^SC(HL,0))'=0 D EN^DDIOL($G(^SC(HL,0))) " +
        //               "I $D(^SC(HL,99))'=0 D EN^DDIOL($G(^SC(HL,99))) ";
        //    return query;
        // }

        internal DdrLister buildGetAppointmentsByDdrQuery(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            DdrLister query = new DdrLister(cxn);
            query.File = "2.98";

            //Took out .01E because it blows up the query when it's a zombie pointer
            //We'll get the Hospital Location name conditionally with Id.
            //Leaving 9.5E in there since it points to APPOINTMENT TYPE which seems
            //like it should be stable.
            string sFlds = ".001;.01;3;5;6;7;9;9.5";
            query.Fields = sFlds;
            query.Flags = "IP";
            query.Iens = "," + dfn + ",";
            query.Xref = "#";

            //NB: the calculation for current status (CURRENT^SDAMU) HAS to happen
            //AFTER setting HL
            //query.Id = "S D0=DA(1),D1=DA," +
            //            "HL=$P(^(0),U,1) " +
            //            "D CURRENT^SDAMU D EN^DDIOL(X) " +
            //            "I $D(^SC(HL,99))=1 " +
            //            "S PH=$P($G(^SC(HL,99)),U,1)," +
            //            "DV=$P($G(^SC(HL,0)),U,15) " +
            //            "S NM=$P($G(^DG(40.8,DV,0)),U,1) " +
            //            "S LN=$$GET1^DIQ(44,HL_\",\",.01) " +
            //            "D EN^DDIOL($G(PH)_\"|\"_NM_\"|\"_LN)";
            query.Id = "S D0=DA(1),D1=DA,HL=$P(^(0),U,1) " +
                "D CURRENT^SDAMU D EN^DDIOL(X) " +
                "I $D(^SC(HL,0))'=0 D EN^DDIOL($P(^SC(HL,0),U,1)_U_$P(^SC(HL,0),U,15)) " + // see ticket #2848 - invalid global ^SC(81,0) at site 556
                "I $D(^SC(HL,99))'=0 D EN^DDIOL(^SC(HL,99))";
            return query;
        }

        // Ticket 2752 - also see VistaEncounterX.testTicket2752
        //internal Appointment[] toAppointmentsFromDdrPre2752(string[] response)
        //{
        //    if (response == null)
        //    {
        //        return null;
        //    }
        //    StringDictionary divisions = cxn.SystemFileHandler.getLookupTable(VistaConstants.MEDICAL_CENTER_DIVISION);
        //    StringDictionary apptTypes = cxn.SystemFileHandler.getLookupTable(VistaConstants.APPT_TYPES);

        //    ArrayList lst = new ArrayList();
        //    for (int recnum = 0; recnum < response.Length; recnum++)
        //    {
        //        string[] parts = StringUtils.split(response[recnum], "~");
        //        string[] flds = StringUtils.split(parts[0], StringUtils.CARET);
        //        Appointment appt = new Appointment();
        //        appt.Id = "A;" + flds[0] + ";" + flds[1];
        //        appt.Facility = cxn.DataSource.SiteId;
        //        appt.Timestamp = VistaTimestamp.toUtcString(flds[0]);
        //        appt.Clinic = new HospitalLocation(flds[1], "");
        //        appt.Status = decodeApptStatus(flds[2]);
        //        if (flds[3] != "")
        //        {
        //            appt.LabDateTime = VistaTimestamp.toUtcString(flds[3]);
        //        }
        //        if (flds[4] != "")
        //        {
        //            appt.XrayDateTime = VistaTimestamp.toUtcString(flds[4]);
        //        }
        //        if (flds[5] != "")
        //        {
        //            appt.EkgDateTime = VistaTimestamp.toUtcString(flds[5]);
        //        }
        //        appt.Purpose = decodeApptPurpose(flds[6]);
        //        if (flds[7] != "")
        //        {
        //            appt.Type = apptTypes[flds[7]];
        //        }
        //        if (flds.Length == 9)
        //        {
        //            appt.CurrentStatus = flds[8];
        //        }

        //        // Part II
        //        if (parts.Length > 1)
        //        {
        //            parts[1] = parts[1].Replace("&#94;", "^");
        //            flds = StringUtils.split(parts[1], StringUtils.CARET);
        //            appt.Clinic.Name = flds[0];
        //            //appt.Clinic.Type = getClinicTypeText(flds[2]);
        //            //appt.Clinic.StopCode = flds[6];
        //            //appt.Clinic.Service = new KeyValuePair<string, string>(flds[7], services[flds[7]]);
        //            //appt.Clinic.PhysicalLocation = flds[10];
        //            if (flds.Length > 14 && !String.IsNullOrEmpty(flds[14]))
        //            {
        //                appt.Clinic.Facility = new Site();
        //                if (divisions.ContainsKey(flds[14]))
        //                {
        //                    appt.Clinic.Facility.Name = divisions[flds[14]];
        //                }
        //            }

        //            //appt.CurrentStatus = flds[10];
        //            //temporary kludge for NCHI:
        //            //appt.Status = appt.CurrentStatus;
        //        }

        //        // Part III
        //        if (parts.Length > 2)
        //        {
        //            parts[2] = parts[2].Replace("&#94;", "^");
        //            flds = StringUtils.split(parts[2], StringUtils.CARET);
        //            appt.Clinic.Phone = flds[0];
        //        }
        //        lst.Add(appt);
        //    }
        //    return (Appointment[])lst.ToArray(typeof(Appointment));
        //}

        internal Appointment[] toAppointmentsFromDdr(string[] response)
        {
            if (response == null || response.Length <= 0)
            {
                return new Appointment[0];
            }
            StringDictionary divisions = cxn.SystemFileHandler.getLookupTable(VistaConstants.MEDICAL_CENTER_DIVISION);
            StringDictionary apptTypes = cxn.SystemFileHandler.getLookupTable(VistaConstants.APPT_TYPES);

            ArrayList lst = new ArrayList();
            for (int recnum = 0; recnum < response.Length; recnum++)
            {
                string[] parts = StringUtils.split(response[recnum], "~");
                string[] flds = StringUtils.split(parts[0], StringUtils.CARET);
                Appointment appt = new Appointment();
                appt.Id = "A;" + flds[0] + ";" + flds[1];
                appt.Facility = cxn.DataSource.SiteId;
                appt.Timestamp = VistaTimestamp.toUtcString(flds[0]);
                appt.Clinic = new HospitalLocation(flds[1], "");
                appt.Status = decodeApptStatus(flds[2]);
                if (flds[3] != "")
                {
                    appt.LabDateTime = VistaTimestamp.toUtcString(flds[3]);
                }
                if (flds[4] != "")
                {
                    appt.XrayDateTime = VistaTimestamp.toUtcString(flds[4]);
                }
                if (flds[5] != "")
                {
                    appt.EkgDateTime = VistaTimestamp.toUtcString(flds[5]);
                }
                appt.Purpose = decodeApptPurpose(flds[6]);
                if (flds[7] != "")
                {
                    appt.Type = apptTypes[flds[7]];
                }
                if (flds.Length == 9)
                {
                    appt.CurrentStatus = flds[8];
                }

                // Part II
                if (parts.Length > 1)
                {
                    parts[1] = parts[1].Replace("&#94;", "^");
                    flds = StringUtils.split(parts[1], StringUtils.CARET);
                    appt.Clinic.Name = flds[0];
                    //appt.Clinic.Type = getClinicTypeText(flds[2]);
                    //appt.Clinic.StopCode = flds[6];
                    //appt.Clinic.Service = new KeyValuePair<string, string>(flds[7], services[flds[7]]);
                    //appt.Clinic.PhysicalLocation = flds[10];
                    if (flds.Length > 1 && !String.IsNullOrEmpty(flds[1]))
                    {
                        appt.Clinic.Facility = new Site();
                        if (divisions.ContainsKey(flds[1]))
                        {
                            appt.Clinic.Facility.Name = divisions[flds[1]];
                        }
                    }

                    //appt.CurrentStatus = flds[10];
                    //temporary kludge for NCHI:
                    //appt.Status = appt.CurrentStatus;
                }

                // Part III
                if (parts.Length > 2)
                {
                    parts[2] = parts[2].Replace("&#94;", "^");
                    flds = StringUtils.split(parts[2], StringUtils.CARET);
                    appt.Clinic.Phone = flds[0];
                }
                lst.Add(appt);
            }
            return (Appointment[])lst.ToArray(typeof(Appointment));
        }

        public Appointment[] getFutureAppointments()
        {
            return getFutureAppointments(cxn.Pid);
        }

        public Appointment[] getFutureAppointments(string dfn)
        {
            Appointment[] appts = getAppointmentsByDdr(dfn);
            ArrayList lst = new ArrayList();
            DateTime today = DateTime.Today;
            for (int i = 0; i < appts.Length; i++)
            {
                int yr = Convert.ToInt16(appts[i].Timestamp.Substring(0, 4));
                int mo = Convert.ToInt16(appts[i].Timestamp.Substring(4, 2));
                int dy = Convert.ToInt16(appts[i].Timestamp.Substring(6, 2));
                DateTime dt = new DateTime(yr, mo, dy);
                if (dt == today || appts[i].CurrentStatus == "FUTURE")
                {
                    lst.Add(appts[i]);
                }
            }
            return (Appointment[])lst.ToArray(typeof(Appointment));
        }

        #endregion

        #region Inpatient Movements

        internal DdrLister buildBasicGetInpatientMovesQuery()
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "405";
            query.Fields = ".01;.02;.03;.04;.06;.07;.09;.14;.17;.18;.19;.24;201;202;203;.1;.16;.11;.25";
            query.Flags = "IP";
            query.Id = "S DZ=$P(^(0),U,19) I DZ'=\"\",($D(^VA(200,DZ,0))#10)'=0 D EN^DDIOL($P($G(^VA(200,DZ,0)),U,1))";
            return query;
        }

        /* Patient Movement Fields
         * 
         *  0:  IEN
         *  1:  DATE/TIME                       .01     Timestamp
         *  2:  TRANSACTION                     .02     Transaction
         *  3:  PATIENT                         .03     Patient.LocalPid
         *  4:  TYPE OF MOVEMENT                .04     MovementType
         *  5:  WARD LOCATION                   .06     AssignedLocation.Id
         *  6:  ROOM-BED                        .07     AssignedLocation.Room, AssignedLocation.Bed
         *  7:  FACILITY TREATING SPECIALTY     .09     Specialty, Service
         *  8:  ADMISSION/CHECK-IN MOVEMENT     .14     CheckInId
         *  9:  DISCHARGE/CHECK-OUT MOVEMENT    .17     CheckOutId
         * 10:  MAS MOVEMENT TYPE               .18     MasMovementType
         * 11:  ATTENDING PHYSICIAN             .19     Attending.Uid
         * 12:  RELATED PHYSICAL MOVEMENT       .24     RelatedPhysicalMovementId
         * 13:  LENGTH OF STAY                  201     LengthOfStay
         * 14:  PASS DAYS                       202     PassDays
         * 15:  DAYS ABSENT                     203     DaysAbsent
         * 16:  DIAGNOSIS [SHORT]               .1      Diagnosis
         * 17:  PTF ENTRY                       .16     PatientTxId
         * 18:  ADMITTED FOR SC CONDITION?      .11     AdmittedForScCondition
         * 19:  SCHEDULED ADMISSION?            .25     ScheduledAdmission
         * 20:  ^VA(200,                        (0,1)   Admitting.Name
         */

        internal Adt[] toAdt(string[] response)
        {
            if (response == null || response.Length == 0)
            {
                return null;
            }
            StringDictionary facilityMovementTypes = cxn.SystemFileHandler.getLookupTable(VistaConstants.FACILITY_MOVEMENT_TYPES);
            StringDictionary masMovementTypes = cxn.SystemFileHandler.getLookupTable(VistaConstants.MAS_MOVEMENT_TYPES);
            //Dictionary<string, TreatingSpecialty> treatingSpecialties = getTreatingSpecialties();
            Dictionary<string, object> treatingSpecialties = cxn.SystemFileHandler.getFile(VistaConstants.TREATING_SPECIALTY); 
            StringDictionary wardLocations = cxn.SystemFileHandler.getLookupTable(VistaConstants.WARD_LOCATIONS);
            StringDictionary roomBeds = cxn.SystemFileHandler.getLookupTable(VistaConstants.ROOM_BEDS);

            Adt[] result = new Adt[response.Length];
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);
                result[i] = new Adt();
                result[i].Id = flds[0];
                if (flds[1] != "")
                {
                    result[i].Timestamp = VistaTimestamp.toDateTime(flds[1]);
                }
                result[i].Transaction = decodePatientMovementTransaction(flds[2]);
                result[i].Patient = new Patient();
                result[i].Patient.LocalPid = flds[3];
                if (facilityMovementTypes.ContainsKey(flds[4]))
                {
                    result[i].MovementType = facilityMovementTypes[flds[4]];
                }
                if (wardLocations.ContainsKey(flds[5]))
                {
                    result[i].AssignedLocation = new HospitalLocation();
                    result[i].AssignedLocation.Id = flds[5];
                    result[i].AssignedLocation.Name = wardLocations[flds[5]];
                }
                if (roomBeds.ContainsKey(flds[6]))
                {
                    string[] parts = StringUtils.split(roomBeds[flds[6]], "-");
                    if (result[i].AssignedLocation == null)
                    {
                        result[i].AssignedLocation = new HospitalLocation();
                    }
                    result[i].AssignedLocation.Room = parts[0];
                    result[i].AssignedLocation.Bed = parts[1];
                }
                if (treatingSpecialties.ContainsKey(flds[7]))
                {
                    TreatingSpecialty ts = (TreatingSpecialty)treatingSpecialties[flds[7]];
                    result[i].Specialty = new KeyValuePair<string, string>(flds[7], ts.Name);
                    result[i].Service = new KeyValuePair<string, string>(ts.Service.Key, ts.Service.Value);
                }
                result[i].CheckInId = flds[8];
                result[i].CheckOutId = flds[9];
                if (masMovementTypes.ContainsKey(flds[10]))
                {
                    result[i].MasMovementType = new KeyValuePair<string, string>(flds[10], masMovementTypes[flds[10]]);
                }
                if (flds[11] != "")
                {
                    result[i].Attending = new User();
                    result[i].Attending.Uid = flds[11];
                    if (flds.Length > 19)
                    {
                        result[i].Attending.Name = new PersonName(flds[20]);
                    }
                }
                result[i].RelatedPhysicalMovementId = flds[12];
                result[i].LengthOfStay = flds[13];
                result[i].PassDays = flds[14];
                result[i].DaysAbsent = flds[15];
                result[i].Diagnosis = flds[16];
                result[i].PatientTxId = flds[17];
                result[i].AdmittedForScCondition = (flds[18] == "1" ? true : false);
                result[i].ScheduledAdmission = (flds[19] == "1" ? true : false);
            }
            return result;
        }

        public Adt[] getInpatientMoves()
        {
            return getInpatientMoves(cxn.Pid);
        }

        public Adt[] getInpatientMoves(string dfn)
        {
            DdrLister query = buildGetInpatientMovesByDfnQuery(dfn);
            string[] response = query.execute();
            if (response.Length == 0)
            {
                return new Adt[] { };
            }
            return toAdt(response);
        }

        internal DdrLister buildGetInpatientMovesByDfnQuery(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            DdrLister query = buildBasicGetInpatientMovesQuery();
            query.From = VistaUtils.adjustForNumericSearch(dfn);
            query.Part = dfn;
            query.Xref = "C";
            return query;
        }

        public InpatientStay getStayMovements(string checkinId)
        {
            DdrLister query = buildGetInpatientMovesByCheckinIdQuery(checkinId);
            string[] response = query.execute();
            if (response == null || response.Length == 0)
            {
                return null;
            }
            Adt[] adts = toAdt(response);
            VistaPatientDao patientDao = new VistaPatientDao(cxn);
            Patient p = patientDao.select(adts[0].Patient.LocalPid);
            InpatientStay result = new InpatientStay();
            result.MovementCheckinId = checkinId;
            result.Adts = adts;
            result.Patient = p;
            return result;
        }

        public Adt[] getInpatientMovesByCheckinId(string checkinId)
        {
            DdrLister query = buildGetInpatientMovesByCheckinIdQuery(checkinId);
            string[] response = query.execute();
            if (response.Length == 0)
            {
                return new Adt[] { };
            }
            return toAdt(response);
        }

        internal DdrLister buildGetInpatientMovesByCheckinIdQuery(string checkinId)
        {
            VistaUtils.CheckRpcParams(checkinId);
            DdrLister query = buildBasicGetInpatientMovesQuery();
            query.From = VistaUtils.adjustForNumericSearch(checkinId);
            query.Part = checkinId;
            query.Xref = "CA";
            return query;
        }

        public Adt[] getInpatientMoves(string fromDate, string toDate)
        {
            DdrLister query = buildGetInpatientMovesByDateRangeQuery(fromDate, toDate);
            string[] response = query.execute();
            if (response.Length == 0)
            {
                return new Adt[] { };
            }
            return toAdt(response);
        }

        internal DdrLister buildGetInpatientMovesByDateRangeQuery(string fromDate, string toDate)
        {
            DateUtils.CheckDateRange(fromDate, toDate);

            DdrLister query = buildBasicGetInpatientMovesQuery();
            //string myFromDate = fromDate;
#if REFACTORING
            string myFromDate = buildFromDate(fromDate);
#else
            if (fromDate.IndexOf('.') == -1)
            {
                myFromDate += ".000001";
            }
            else if (fromDate.EndsWith("0"))
            {
                myFromDate = myFromDate.Substring(0, myFromDate.Length - 1) + "1";
            } 
#endif // REFACTORING
	
            query.From = VistaTimestamp.fromUtcString(myFromDate);
            query.Xref = "B";
            query.Screen = VistaUtils.buildFromToDateScreenParam(fromDate, toDate, 0, 1);
            return query;
        }

        /// <summary>Takes care of some of the DDR lister quirks.
        /// </summary>
        /// <remarks>
        /// The aforementioned quirks include:
        /// 1. if there's no HHmmss component, it needs one
        /// 2. you can't end in 00 seconds, so increment +1 (? should it be -1 -- NEED TO ASK JOE)
        /// 3. starts after the value, so should be -1 (?)
        /// 
        /// So what do you do if you want to start at 1 second? subtracting 1 would get you to a time ending in 0,
        /// which we can't do...
        /// </remarks>
        /// <param name="fromDate"></param>
        /// <returns></returns>
        internal string buildFromDate(string fromDate)
        {
            // put date in a format we can manipulate
            DateTime myFromDate = DateUtils.IsoDateStringToDateTime(fromDate);
            // set the start time forward so that DDR LISTER catches the first desired time
            // TBD VAN: should be able to refactor
            myFromDate = myFromDate.Subtract(new TimeSpan(0, 0, 1));
            // if that sets it to end in a '0' then subtract another second...which could cause a problem.
            if (myFromDate.Second % 10 == 0)
            {
                myFromDate = myFromDate.Subtract(new TimeSpan(0, 0, 1));
            }
            return myFromDate.ToString("yyyyMMdd.HHmmss");
        }

        /// <summary>Iterates through dates and gets the inpatient moves for those days 
        /// </summary> 
        /// <param name="fromDate">Inclusive start date</param> 
        /// <param name="toDate">Non-inclusive end date</param> 
        /// <returns>Adt array, with length of 0 and no Adts if empty set</returns> 
        public Adt[] getInpatientMoves(string fromDate, string toDate, string iterLength)
        {
            ArrayList allAdts = new ArrayList();
            TimeSpan interval = VistaDateTimeIterator.IterationTimeSpanFromString(iterLength);
            // these are all the same for every query... 

            DdrLister query = buildBasicGetInpatientMovesQuery();
            query.Xref = "B";

#if REFACTORING
            VistaDateTimeIterator iteratingDate
                = new VistaDateTimeIterator(DateUtils.IsoDateStringToDateTime(fromDate)
                                            , DateUtils.IsoDateStringToDateTime(toDate)
                                            , interval
                                            );

            while (!iteratingDate.IsDone())
            {
                iteratingDate.SetIterEndDate();
                query.From = iteratingDate.GetDdrListerPart();
                // part restricts matches, so you actually need to do some iterating and walk through 
                // the toDate will be used in the iteration, and the part will be the same as the iterating date 
                // which starts as the fromDate. 
                query.Part = iteratingDate.GetDdrListerPart();
                string[] rtn = query.execute();
                if (rtn != null && rtn.Length != 0)
                {
                    allAdts.AddRange(toAdt(rtn));
                }
                iteratingDate.AdvanceIterStartDate();
            }
#else 
            // get initial date strings and convert 
            // TBD VAN consider custom formatter or extract to DateUtils 
            DateTime iteratingDate = DateUtils.IsoDateStringToDateTime(fromDate); 
            // non-inclusive end iteration 
            DateTime endDate = DateUtils.IsoDateStringToDateTime(toDate); 
            while (iteratingDate.CompareTo(endDate) < 0) 
            { 
                string iteratingDateStr = iteratingDate.ToString("yyyyMMdd"); 
                query.From = VistaTimestamp.fromUtcString(iteratingDateStr); 
                // part restricts matches, so you actually need to do some iterating and walk through 
                // the toDate will be used in the iteration, and the part will be the same as the iterating date 
                // which starts as the fromDate. 
                query.Part = VistaTimestamp.fromUtcString(iteratingDateStr); 
 
                String[] rtn = query.execute(); 
                if (!Object.Equals(rtn, null)) 
                { 
                    allAdts.AddRange(MakeAdts(rtn)); 
                } 
                DateTime nextDate = iteratingDate.Add(interval); 
                iteratingDate = nextDate; 
            } 
#endif // REFACTORING
            return (Adt[])allAdts.ToArray(typeof(Adt));
        }
        
        public IndexedHashtable getUniqueInpatientMovementIds(string fromTS, string toTS)
        {
            DdrLister query = buildGetUniqueInpatientMovementIdsQuery(fromTS, toTS);
            string[] response = query.execute();
            response = trimToInterval(response, fromTS, toTS);
            return toUniqueCheckinIds(response);
        }

        internal DdrLister buildGetUniqueInpatientMovementIdsQuery(string fromTS, string toTS)
        {
            DateUtils.CheckDateRange(fromTS, toTS);

            DdrLister query = new DdrLister(cxn);
            query.File = "405";
            query.Fields = ".01;.14";
            query.Flags = "IP";
            string sdate = DateUtils.trimTime(fromTS);
            query.From = VistaTimestamp.fromUtcString(sdate);
            query.Part = VistaTimestamp.fromUtcString(sdate);
            query.Xref = "B";
            return query;
        }

        internal string[] trimToInterval(string[] response, string fromTS, string toTS)
        {
            ArrayList lst = new ArrayList(response.Length);
            fromTS = VistaTimestamp.fromUtcString(fromTS);
            toTS = VistaTimestamp.fromUtcString(toTS);
            DateTime fromDT = VistaTimestamp.toDateTime(fromTS);
            DateTime toDT = VistaTimestamp.toDateTime(toTS);
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);
                DateTime thisDT = VistaTimestamp.toDateTime(flds[1]);
                if (thisDT >= fromDT && thisDT < toDT)
                {
                    lst.Add(flds[2]);
                }
            }
            return (string[])lst.ToArray(typeof(string));
        }

        internal IndexedHashtable toUniqueCheckinIds(string[] response)
        {
            if (response == null || response.Length == 0)
            {
                return null;
            }
            IndexedHashtable t = new IndexedHashtable(response.Length);
            for (int i = 0; i < response.Length; i++)
            {
                if (String.IsNullOrEmpty(response[i]))
                {
                    continue; // per http://trac.medora.va.gov/web/ticket/2713 - found a blank record in a test site. can't do anything with stay since can't locate checkin id 
                }
                if (t.ContainsKey(response[i]))
                {
                    continue;
                }
                t.Add(response[i], null);
            }
            return t;
        }

        public InpatientStay[] getStayMovementsByDateRange(string fromTS, string toTS)
        {
            IndexedHashtable checkinIds = getUniqueInpatientMovementIds(fromTS, toTS);
            if (checkinIds == null || checkinIds.Count == 0)
            {
                return new InpatientStay[] { };
            }
            ArrayList lst = new ArrayList(checkinIds.Count);
            VistaPatientDao patientDao = new VistaPatientDao(cxn);
            for (int i = 0; i < checkinIds.Count; i++)
            {
                Adt[] adts = getInpatientMovesByCheckinId((string)checkinIds.GetKey(i));
                Patient p = patientDao.select(adts[0].Patient.LocalPid);
                InpatientStay s = new InpatientStay();
                s.Adts = adts;
                s.Patient = p;
                s.MovementCheckinId = (string)checkinIds.GetKey(i);
                lst.Add(s);
            }
            return (InpatientStay[])lst.ToArray(typeof(InpatientStay));
        }

        public InpatientStay[] getStayMovementsByPatient(string dfn)
        {
            VistaPatientDao pDao = new VistaPatientDao(cxn);

            // first get all ADT's for pid
            Adt[] adts = getInpatientMoves(dfn);
            if (adts.Length == 0)
            {
                return new InpatientStay[] { };
            }

            List<string> checkinIds = new List<string>();
            // build list of unique checkinId's
            foreach (Adt adt in adts)
            {
                if (!checkinIds.Contains(adt.CheckInId))
                {
                    checkinIds.Add(adt.CheckInId);
                }
            }

            VistaPatientDao patientDao = new VistaPatientDao(cxn);
            ArrayList lst = new ArrayList();
            for (int i = 0; i < checkinIds.Count; i++)
            {
                Adt[] adtAry = getInpatientMovesByCheckinId((string)checkinIds[i]);
                Patient p = patientDao.select(adtAry[0].Patient.LocalPid);
                InpatientStay s = new InpatientStay();
                s.Adts = adtAry;
                s.Patient = p;
                s.MovementCheckinId = (string)checkinIds[i];
                lst.Add(s);
            }
            return (InpatientStay[])lst.ToArray(typeof(InpatientStay));
        }

        /* TBD - Joel or Joe: this builds an  InpatientStayArray client side from the 
         * first big grab of Adt via getInpatientMoves(dfn) - however, the hashes of the
         * object returned from this function and those returned from getStayMovementsByPatient
         * do NOT match so they are being built differently. Consider building Adt the
         * same in both getInpatientMoves and getInpatientMovedByCheckinId to ensure the
         * objects are identical, we can then probably replace getStayMovementsByPatient with
         * the function below
        public InpatientStay[] getStayMovementsByPatientClientSide(string dfn)
        {
            // first get all ADT's for pid
            Adt[] adts = getInpatientMoves(dfn);
            if(adts.Length == 0)
            {
                return new InpatientStay[] { };
            }

            List<string> checkinIds = new List<string>();
            // build list of unique checkinId's
            foreach(Adt adt in adts)
            {
                if (!checkinIds.Contains(adt.CheckInId))
                {
                    checkinIds.Add(adt.CheckInId);
                }
            }
            VistaPatientDao patientDao = new VistaPatientDao(cxn);
            ArrayList lst2 = new ArrayList();
            for (int j = 0; j < checkinIds.Count; j++)
            {
                ArrayList adtList = new ArrayList();
                InpatientStay stay = new InpatientStay();
                stay.MovementCheckinId = checkinIds[j];
                for (int i = 0; i < adts.Length; i++)
                {
                    if (adts[i].CheckInId == checkinIds[j])
                    {
                        if(stay.Patient == null)
                        {
                            stay.Patient = patientDao.select(adts[i].Patient.LocalPid);
                        }
                        adtList.Add(adts[i]);
                    }
                }
                stay.Adts = (Adt[])adtList.ToArray(typeof(Adt));
                lst2.Add(stay);
            }
            return (InpatientStay[])lst2.ToArray(typeof(InpatientStay));
        }
        */

        #endregion

        #region Visits

        public Visit[] getOutpatientVisits()
        {
            return getOutpatientVisits(cxn.Pid);
        }

        public Visit[] getOutpatientVisits(string dfn)
        {
            DdrLister query = buildGetOutpatientVisitsRequest(dfn);
            string[] response = query.execute();
            return toOutpatientVisits(response);
        }

        internal DdrLister buildGetOutpatientVisitsRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            DdrLister query = new DdrLister(cxn);
            query.File = "9000010.07";

            // E flag note (10/16/08, joe): this call was only used by MHV SMS which didn't happen.
            // If we ever do find a need for it though, these E flags should be cleaned up.
            query.Fields = ".01;.01E;.019;.02;.02E;.12";
            query.Flags = "IP";
            query.From = VistaUtils.adjustForNumericSearch(cxn.Pid);
            query.Part = cxn.Pid;
            query.Xref = "C";
            query.Max = "20";
            return query;
        }

        internal Visit[] toOutpatientVisits(string[] response)
        {
            if (response.Length == 0)
            {
                return null;
            }
            Visit[] result = new Visit[response.Length];
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);
                result[i] = new Visit();
                result[i].Id = flds[0];
            }
            return result;
        }

        public Visit[] getVisits(string fromDate, string toDate)
        {
            return getVisits(cxn.Pid, fromDate, toDate);
        }

        public Visit[] getVisits(string dfn, string fromDate, string toDate)
        {
            MdoQuery request = buildGetVisitsRequest(dfn, fromDate, toDate);
            string response = (string)cxn.query(request);
            return toVisits(response);
        }

        internal MdoQuery buildGetVisitsRequest(string dfn, string fromDate, string toDate)
        {
            if(!DateUtils.isWellFormedUtcDateTime(fromDate))
                throw new MdoException(MdoExceptionCode.ARGUMENT_DATE_FORMAT, "Invalid 'from' date: " + fromDate);

            VistaUtils.CheckRpcParams(dfn, fromDate, toDate);

            VistaQuery vq = new VistaQuery("ORWCV VST");
            vq.addParameter(vq.LITERAL, dfn);
            vq.addParameter(vq.LITERAL, VistaTimestamp.fromUtcString(fromDate));
            //5/3/2011 DP an empty toDate is now part of a valid call to the RPC, it is essentially a toDate 
            //of today.
            //For testing purposes we do not substitute today's date as we will not have a constant RPC call 
            //signature to test.
            if (toDate == "")
                { vq.addParameter(vq.LITERAL, toDate); }
            else
               { vq.addParameter(vq.LITERAL, VistaTimestamp.fromUtcToDate(toDate)); }
            vq.addParameter(vq.LITERAL, "1");
            return vq;
        }

        internal Visit[] toVisits(string response)
        {
            if (response == "")
            {
                return new Visit[0];
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            Visit[] result = new Visit[lines.Length];
            for (int i = 0; i < lines.Length; i++)
            {
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                result[i] = new Visit();
                string[] subflds = StringUtils.split(flds[0], StringUtils.SEMICOLON);
                result[i].Type = subflds[0];
                result[i].Timestamp = VistaTimestamp.toUtcString(subflds[1]);
                result[i].Location = new HospitalLocation(subflds[2], flds[2]);
                if (flds.Length > 3)
                {
                    result[i].Status = flds[3];
                }
                else
                {
                    result[i].Status = "NO STATUS";
                }
            }
            return result;
        }

        public Visit[] getVisitsForDay(string theDate)
        {
            DdrLister query = buildGetVisitsForDayRequest(theDate);
            string[] response = query.execute();
            return toVisitsForDay(response);
        }

        internal DdrLister buildGetVisitsForDayRequest(string theDate)
        {
            if(!DateUtils.isWellFormedUtcDateTime(theDate))
                throw new MdoException(MdoExceptionCode.ARGUMENT_DATE_FORMAT, "Invalid date: " + theDate);

            DdrLister query = new DdrLister(cxn);
            query.File = "9000010";
            query.Fields = ".01;.05";
            query.Flags = "IP";
            string vistaTS = VistaTimestamp.fromUtcString(theDate);
            query.From = vistaTS;
            query.Part = vistaTS;
            query.Xref = "B";
            //query.Max = "5";
            return query;
        }

        internal Visit[] toVisitsForDay(string[] response)
        {
            if (response.Length == 0)
            {
                return null;
            }
            Visit[] result = new Visit[response.Length];
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);
                result[i] = new Visit();
                result[i].Id = flds[0];
                result[i].Patient = new Patient();
                result[i].Patient.LocalPid = flds[2];
            }
            return result;
        }

        public string getVisitIdByTimestamp(string timestamp)
        {
            if (!DateUtils.isWellFormedUtcDateTime(timestamp))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_DATE_FORMAT, "Invalid timestamp: " + timestamp);
            }

            string arg = "$O(^AUPNVSIT(\"B\"," + VistaTimestamp.fromUtcString(timestamp) + ",0))";
            return VistaUtils.getVariableValue(cxn, arg);
        }

        public Visit[] getMentalHealthVisits()
        {
            throw new NotImplementedException();
        }

        #endregion

        #region Hospital Locations (move to VistaLocationDao)

        internal DdrLister buildGetHospitalLocationsQuery()
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "44";

            // E flag note (10/16/08, joe): 3E is institution name.  Leave for now since...
            //  1) should be pretty stable
            //  2) little used function
            //  3) the screen param should skip disused records
            query.Fields = ".01;1;2;2.1;3;3E;3.5;4;6;7;8;9;9.5;10;42;99;1916";
            query.Flags = "IP";
            query.Xref = "#";

            // It would be nice to screen by inactive date instead of checking the name for ZZ  which is
            // only a convention, but inactive date does not seem to be used.
            query.Screen = "I $E($P(^(0),U,1),1,2)'=\"ZZ\"";
            return query;
        }

        internal Dictionary<string, HospitalLocation> toHospitalLocationDictionary(string[] response)
        {
            StringDictionary locationTypes = cxn.SystemFileHandler.getLookupTable(VistaConstants.LOCATION_TYPES);
            StringDictionary divisions = cxn.SystemFileHandler.getLookupTable(VistaConstants.MEDICAL_CENTER_DIVISION);
            StringDictionary stops = cxn.SystemFileHandler.getLookupTable(VistaConstants.CLINIC_STOPS);
            Dictionary<string, object> specialties = cxn.SystemFileHandler.getFile(VistaConstants.TREATING_SPECIALTY);
            StringDictionary wardLocations = cxn.SystemFileHandler.getLookupTable(VistaConstants.WARD_LOCATIONS);

            Dictionary<string, HospitalLocation> result = new Dictionary<string, HospitalLocation>(response.Length);
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);
                HospitalLocation hl = new HospitalLocation(flds[0], flds[1]);
                hl.Abbr = flds[2];
                hl.Type = decodeHospitalLocationType(flds[3]);
                if (locationTypes.ContainsKey(flds[4]))
                {
                    hl.TypeExtension = new KeyValuePair<string, string>(flds[4], locationTypes[flds[4]]);
                }
                hl.Facility = new Site(flds[5], flds[6]);
                if (divisions.ContainsKey(flds[7]))
                {
                    hl.Division = new KeyValuePair<string, string>(flds[7], divisions[flds[7]]);
                }
                hl.Module = new KeyValuePair<string, string>(flds[8], "");
                hl.DispositionAction = decodeHospitalLocationDispositionAction(flds[9]);
                hl.VisitLocation = flds[10];
                if (stops.ContainsKey(flds[11]))
                {
                    hl.StopCode = new KeyValuePair<string, string>(flds[11], stops[flds[11]]);
                }
                hl.Service = new KeyValuePair<string, string>(flds[12], decodeHospitalLocationService(flds[12]));
                if (specialties.ContainsKey(flds[13]))
                {
                    TreatingSpecialty ts = (TreatingSpecialty)specialties[flds[13]];
                    hl.Specialty = new KeyValuePair<string, string>(flds[13], ts.Name);
                }
                hl.PhysicalLocation = flds[14];
                if (wardLocations.ContainsKey(flds[15]))
                {
                    hl.WardLocation = new KeyValuePair<string, string>(flds[15], wardLocations[flds[15]]);
                }
                hl.Phone = flds[16];
                hl.PrincipalClinic = new KeyValuePair<string, string>(flds[17], "");
                result.Add(flds[0], hl);
            }
            Dictionary<string, HospitalLocation>.Enumerator enm = result.GetEnumerator();
            while (enm.MoveNext())
            {
                HospitalLocation hl = enm.Current.Value;
                if (result.ContainsKey(hl.Module.Key))
                {
                    hl.Module = new KeyValuePair<string, string>(hl.Module.Key, result[hl.Module.Key].Name);
                }
                if (result.ContainsKey(hl.PrincipalClinic.Key))
                {
                    hl.PrincipalClinic = new KeyValuePair<string, string>(hl.PrincipalClinic.Key, result[hl.PrincipalClinic.Key].Name);
                }
            }
            return result;
        }

        public StringDictionary lookupHospitalLocations(string target)
        {
            DdrLister query = buildLookupHospitalLocationsQuery(target);
            string[] response = query.execute();
            return VistaUtils.toStringDictionary(response);
        }

        internal DdrLister buildLookupHospitalLocationsQuery(string target)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "44";
            query.Fields = ".01";
            query.Flags = "IP";
            query.Xref = "B";
            query.From = VistaUtils.adjustForNameSearch(target);
            query.Part = target;

            // It would be nice to screen by inactive date instead of checking the name for ZZ  which is
            // only a convention, but inactive date does not seem to be used.
            query.Screen = "I $E($P(^(0),U,1),1,2)'=\"ZZ\"";
            return query;
        }

        public HospitalLocation getHospitalLocation(string ien)
        {
            VistaUtils.CheckRpcParams(ien);

            DdrLister query = buildGetHospitalLocationsQuery();
            query.From = VistaUtils.adjustForNumericSearch(ien);
            query.Part = ien;
            query.Max = "1";
            string[] response = query.execute();
            if (response == null || response.Length == 0)
            {
                return null;
            }
            return toHospitalLocation(response[0]);
        }

        internal HospitalLocation toHospitalLocation(string response)
        {
            if (response == "")
            {
                return null;
            }
            StringDictionary locationTypes = cxn.SystemFileHandler.getLookupTable(VistaConstants.LOCATION_TYPES);
            StringDictionary divisions = cxn.SystemFileHandler.getLookupTable(VistaConstants.MEDICAL_CENTER_DIVISION);
            StringDictionary stops = cxn.SystemFileHandler.getLookupTable(VistaConstants.CLINIC_STOPS);
            Dictionary<string, object> specialties = cxn.SystemFileHandler.getFile(VistaConstants.TREATING_SPECIALTY);
            StringDictionary wardLocations = cxn.SystemFileHandler.getLookupTable(VistaConstants.WARD_LOCATIONS);

            string[] flds = StringUtils.split(response, StringUtils.CARET);
            HospitalLocation result = new HospitalLocation(flds[0], flds[1]);
            result.Abbr = flds[2];
            result.Type = decodeHospitalLocationType(flds[3]);
            if (locationTypes.ContainsKey(flds[4]))
            {
                result.TypeExtension = new KeyValuePair<string,string>(flds[4], locationTypes[flds[4]]);
            }
            result.Facility = new Site(flds[5], flds[6]);
            if (divisions.ContainsKey(flds[7]))
            {
                result.Division = new KeyValuePair<string, string>(flds[7], divisions[flds[7]]);
            }
            result.Module = new KeyValuePair<string, string>(flds[8], "");
            result.DispositionAction = decodeHospitalLocationDispositionAction(flds[9]);
            result.VisitLocation = flds[10];
            if (stops.ContainsKey(flds[11]))
            {
                result.StopCode = new KeyValuePair<string, string>(flds[11], stops[flds[11]]);
            }
            result.Service = new KeyValuePair<string, string>(flds[12], decodeHospitalLocationService(flds[12]));
            if (specialties.ContainsKey(flds[13]))
            {
                TreatingSpecialty ts = (TreatingSpecialty)specialties[flds[13]];
                result.Specialty = new KeyValuePair<string, string>(flds[13], ts.Name);
            }
            result.PhysicalLocation = flds[14];
            if (wardLocations.ContainsKey(flds[15]))
            {
                result.WardLocation = new KeyValuePair<string, string>(flds[15], wardLocations[flds[15]]);
            }
            result.Phone = flds[16];
            result.PrincipalClinic = new KeyValuePair<string, string>(flds[17], "");
            return result;
        }

        // Depreciate to lookupHospitalLocations
        public HospitalLocation[] lookupLocations(string target, string direction)
        {
            MdoQuery request = buildLookupLocationsRequest(target, direction);
            string response = (string)cxn.query(request);
            return toLocations(response);
        }

        internal MdoQuery buildLookupLocationsRequest(string target, string direction)
        {
            VistaQuery vq = new VistaQuery("ORWU1 NEWLOC");
            if (target == null || target == "")
                vq.addParameter(vq.LITERAL, "");
            else
                vq.addParameter(vq.LITERAL, VistaUtils.adjustForNameSearch(target.ToUpper()));
            vq.addParameter(vq.LITERAL, VistaUtils.setDirectionParam(direction));
            return vq;
        }

        internal HospitalLocation[] toLocations(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            ArrayList lst = new ArrayList(lines.Length);
            for (int i = 0; i < lines.Length; i++)
            {
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                lst.Add(new HospitalLocation(flds[0],flds[1]));
            }
            return (HospitalLocation[])lst.ToArray(typeof(HospitalLocation));
        }

        // This is used when the client app knows the location name
        public string getLocationId(string locationName)
        {
            if (String.IsNullOrEmpty(locationName))
                return "";

            string arg = "$O(^SC(\"B\",\"" + locationName + "\",0))";
            return VistaUtils.getVariableValue(cxn, arg);
        }

        public string getLocation(string ien)
        {
            VistaUtils.CheckRpcParams(ien);
            string arg = "$G(^SC(" + ien + ",0))";
            return VistaUtils.getVariableValue(cxn, arg);
        }

        /// <summary>File 44, field 10 free text string for #2917
        /// </summary>
        /// <param name="ien"></param>
        /// <returns></returns>
        internal string getPhysicalLocationString(string ien)
        {
            VistaUtils.CheckRpcParams(ien);
            string arg = "$P($G(^SC(" + ien + ",0)),U,11)";
            return VistaUtils.getVariableValue(cxn, arg);
        }

        public HospitalLocation[] getWards()
        {
            MdoQuery request = buildGetWardsRequest();
            string response = (string)cxn.query(request);
            return toHospitalLocations(response);
        }

        internal MdoQuery buildGetWardsRequest()
        {
            VistaQuery vq = new VistaQuery("ORQPT WARDS");
            return vq;
        }

        internal HospitalLocation[] toHospitalLocations(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] rex = StringUtils.split(response, StringUtils.CRLF);
            rex = StringUtils.trimArray(rex);
            HospitalLocation[] result = new HospitalLocation[rex.Length];
            for (int i = 0; i < rex.Length; i++)
            {
                if (rex[i] == "")
                {
                    continue;
                }
                string[] flds = StringUtils.split(rex[i], StringUtils.CARET);
                result[i] = new HospitalLocation(flds[0],flds[1]);
            }
            return result;
        }

        public bool isImoLocation(string locationIen, string dfn)
        {
            MdoQuery request = buildIsImoLocationRequest(locationIen, dfn);
            string response = (string)cxn.query(request);
            return response == "1";
        }

        internal MdoQuery buildIsImoLocationRequest(string locationIen, string dfn)
        {
            VistaUtils.CheckRpcParams(locationIen);
            VistaUtils.CheckRpcParams(dfn);
            VistaQuery vq = new VistaQuery("ORIMO IMOLOC");
            vq.addParameter(vq.LITERAL, locationIen);
            vq.addParameter(vq.LITERAL, dfn);
            return vq;
        }

        public HospitalLocation[] getClinics(string target, string direction)
        {
            VistaQuery vq = new VistaQuery("ORWU CLINLOC");
            vq.addParameter(vq.LITERAL, VistaUtils.adjustForNameSearch(target));
            vq.addParameter(vq.LITERAL, (String.IsNullOrEmpty(direction) ? "1" : direction));
            string response = (string)cxn.query(vq);
            return toHospitalLocationsFromClinics(response);
        }

        internal HospitalLocation[] toHospitalLocationsFromClinics(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            HospitalLocation[] result = new HospitalLocation[lines.Length];
            for (int i = 0; i < lines.Length; i++)
            {
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                result[i] = new HospitalLocation(flds[0], flds[1]);
                result[i].PhysicalLocation = getPhysicalLocationString(flds[0]);
            }
            return result;
        }

        public Site[] getSiteDivisions(string sitecode)
        {
            DdrLister query = buildGetSiteDivisionsQuery(sitecode);
            string[] response = query.execute();
            return toSitesFromGetSiteDivisions(response);
        }

        internal DdrLister buildGetSiteDivisionsQuery(string sitecode)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "4";
            query.Fields = "99;.01;13;100";
            query.Flags = "IP";
            query.From = VistaUtils.adjustForNumericSearch(sitecode);
            query.Part = sitecode;
            query.Xref = "D";
            query.Screen = "I $P(^(99),U,4)'=1";
            return query;
        }

        internal Site[] toSitesFromGetSiteDivisions(string[] response)
        {
            if (response == null)
            {
                return null;
            }
            Site[] result = new Site[response.Length];
            StringDictionary facilityTypes = cxn.SystemFileHandler.getLookupTable("4.1");
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);

                //NB - we are not using the IEN for now
                result[i] = new Site(flds[1], flds[2]);
                if (facilityTypes.ContainsKey(flds[3]))
                {
                    result[i].SiteType = facilityTypes[flds[3]];
                }
                result[i].DisplayName = flds[4];
            }
            return result;
        }

        public Ward[] getWardsByDdr()
        {
            DdrLister query = buildGetWardsByDdrQuery();
            string[] response = query.execute();
            return toWardsByDdr(response);
        }

        internal DdrLister buildGetWardsByDdrQuery()
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "42";
            query.Fields = ".01;.017;.2;44";
            query.Flags = "IP";
            query.Xref = "B";
            query.Id = "N R S HL=$P(^(44),U,1) " +
                        "I HL'=\"\",$D(^SC(HL,0))'=0 S R=$P(^SC(HL,0),U,1)_U_$P(^SC(HL,0),U,2) " +
                        "D EN^DDIOL(R) " +
                        "I HL'=\"\",$D(^SC(HL,99))'=0 S R=U_$P(^SC(HL,99),U,1) " +
                        "D EN^DDIOL(R)";
            return query;
        }

        internal Ward[] toWardsByDdr(string[] response)
        {
            ArrayList lst = new ArrayList();
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = response[i].Split(new char[] { '^' });
                if (flds[3] != "1")
                {
                    lst.Add(response[i]);
                }
            }
            if (lst.Count == 0)
            {
                return null;
            }

            Ward[] result = new Ward[lst.Count];

            StringDictionary specialties = cxn.SystemFileHandler.getLookupTable(VistaConstants.PTF_SPECIALTIES);

            for (int i = 0; i < lst.Count; i++)
            {
                string[] flds = ((string)lst[i]).Split(new char[] { '^' });
                result[i] = new Ward();
                result[i].Id = flds[0];
                result[i].WardName = flds[1];
                if (specialties.ContainsKey(flds[2]))
                {
                    result[i].Specialty = new KeyValuePair<string, string>(flds[2], specialties[flds[2]]);
                }
                if (flds[4] != "")
                {
                    string[] hlFlds = StringUtils.split(flds[5], "&#94;");
                    result[i].HospitalLocationName = hlFlds[0];
                    result[i].Abbr = hlFlds[1];
                    result[i].Phone = hlFlds.Length == 3 ? hlFlds[2] : "";
                }
            }
            return result;
        }



        #endregion

        #region Providers

        public DictionaryHashList getSpecialties()
        {
            VistaQuery vq = new VistaQuery("ORQPT SPECIALTIES");
            string response = (string)cxn.query(vq);
            if (response == "")
            {
                return null;
            }
            DictionaryHashList result = new DictionaryHashList();
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            for (int i = 0; i < lines.Length; i++)
            {
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                result.Add(flds[0], flds[1]);
            }
            return result;
        }

        public DictionaryHashList getTeams()
        {
            VistaQuery vq = new VistaQuery("ORQPT TEAMS");
            string response = (string)cxn.query(vq);
            if (response == "")
            {
                return null;
            }
            DictionaryHashList result = new DictionaryHashList();
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            for (int i = 0; i < lines.Length; i++)
            {
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                result.Add(flds[0], flds[1]);
            }
            return result;
        }

        #endregion

        #region Inpatient stays

        public InpatientStay[] getStaysForWard(string wardIen)
        {
            MdoQuery request = buildGetStaysForWardRequest(wardIen);
            string response = (string)cxn.query(request);
            return toInpatientStays(response);
        }

        internal MdoQuery buildGetStaysForWardRequest(string wardIen)
        {
            VistaUtils.CheckRpcParams(wardIen);
            VistaQuery vq = new VistaQuery("ORWPT BYWARD");
            vq.addParameter(vq.LITERAL, wardIen);
            return vq;
        }

        internal InpatientStay[] toInpatientStays(string response)
        {
            if (response == "")
            {
                throw new Exception("Bad return getting patient by ward");
            }
            if (response.StartsWith("^No patients found."))
            {
                return null;
            }
            string[] rex = StringUtils.split(response, StringUtils.CRLF);
            InpatientStay[] stays = new InpatientStay[rex.Length];
            for (int i = 0; i < rex.Length; i++)
            {
                if (rex[i] == "")
                {
                    continue;
                }
                string[] flds = StringUtils.split(rex[i], StringUtils.CARET);
                stays[i] = new InpatientStay();
                stays[i].Patient = new Patient();
                stays[i].Patient.LocalPid = flds[0];
                stays[i].Patient.Name = new PersonName(flds[1]);
                if (flds.Length == 3)
                {
                    stays[i].Location = new HospitalLocation();
                    if (flds[2].IndexOf('-') == -1)
                    {
                        stays[i].Location.Room = flds[2];
                    }
                    else
                    {
                        string[] parts = StringUtils.split(flds[2], "-");
                        stays[i].Location.Room = parts[0];
                        stays[i].Location.Bed = parts[1];
                    }
                }
            }
            return stays;
        }

	    public InpatientStay[] getAdmissions()
        {
            return getAdmissions(cxn.Pid);
        }

        public InpatientStay[] getAdmissions(string dfn)
        {
            MdoQuery request = buildGetAdmissionsRequest(dfn);
            string response = (string)cxn.query(request);
            return toInpatientStays(response, dfn);
        }

        internal MdoQuery buildGetAdmissionsRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            VistaQuery vq = new VistaQuery("ORWPT ADMITLST");
            vq.addParameter(vq.LITERAL, dfn);
            return vq;
        }

        internal InpatientStay[] toInpatientStays(string response, string dfn)
        {
            if (response == "")
            {
                return null;
            }
            string[] rex = StringUtils.split(response, StringUtils.CRLF);
            rex = StringUtils.trimArray(rex);
            InpatientStay[] stays = new InpatientStay[rex.Length];
            for (int i = 0; i < rex.Length; i++)
            {
                String[] flds = StringUtils.split(rex[i], StringUtils.CARET);
                stays[i] = new InpatientStay();
                stays[i].Patient = new Patient();
                stays[i].Patient.LocalPid = dfn;
                stays[i].AdmitTimestamp = VistaTimestamp.toUtcString(flds[0]);
                stays[i].Location = new HospitalLocation(flds[1], flds[2]);
                stays[i].Type = flds[3];
            }
            return stays;
        }

        public Adt[] getInpatientDischarges(string dfn)
        {
            DdrLister query = buildGetInpatientDischargesQuery(dfn);
            string[] response = query.execute();
            if (response.Length == 0)
            {
                //return null; // old implementation
                return new Adt[0];
            }
            return toAdt(response);
        }

        internal DdrLister buildGetInpatientDischargesQuery(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            DdrLister query = buildBasicGetInpatientMovesQuery();
            query.From = VistaUtils.adjustForNumericSearch(dfn);
            query.Part = dfn;
            query.Xref = "C";
            query.Screen = "I $P(^(0),U,2)=3";
            return query;
        }

        #endregion

        #region DRG

        public Drg[] getDRGRecords()
        {
            DdrLister query = buildGetDrgRecordsQuery();
            string[] response = query.execute();
            return toDrg(response);
        }

        internal DdrLister buildGetDrgRecordsQuery()
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "80.2";
            query.Fields = "@";
            query.Flags = "P";
            query.Id = "D EN^DDIOL($G(^ICD($E($P(^(0),U,1),4,99),1,1,0)))";
            return query;
        }

        internal Drg[] toDrg(string[] response)
        {
            if (response.Length == 0)
            {
                return null;
            }
            Drg[] result = new Drg[response.Length];
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);
                result[i] = new Drg(flds[0], flds[1]);
            }
            return result;
        }

        #endregion

        #region Service Connected

        public string getServiceConnectedCategory(string initialCategory, string locationIen, bool outpatient)
        {
            MdoQuery request = buildGetServiceConnectedCategoryRequest(initialCategory, locationIen, outpatient);
            return (string)cxn.query(request);
        }

        internal MdoQuery buildGetServiceConnectedCategoryRequest(string initialCategory, string locationIen, bool outpatient)
        {
            VistaQuery vq = new VistaQuery("ORWPCE GETSVC");
            vq.addParameter(vq.LITERAL, initialCategory);
            vq.addParameter(vq.LITERAL, locationIen);
            vq.addParameter(vq.LITERAL, (outpatient ? "0" : "1"));
            return vq;
        }

        public string getServiceCategoryFromVisit(string visitIEN)
        {
            VistaUtils.CheckRpcParams(visitIEN);
            string arg = "$G(^AUPNVSIT(" + visitIEN + ",0))";
            string response = VistaUtils.getVariableValue(cxn, arg);
            if (response != "")
            {
                response = StringUtils.piece(response, StringUtils.CARET, 7);
            }
            return response;
        }

        #endregion

        #region Reports

        public string getOutpatientEncounterReport(string fromDate, string toDate, int nrpts)
        {
            return getOutpatientEncounterReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getOutpatientEncounterReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetOutpatientEncounterReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetOutpatientEncounterReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_OE:OUTPATIENT ENCOUNTER~;;205;");
        }

        public string getAdmissionsReport(string fromDate, string toDate, int nrpts)
        {
            return getAdmissionsReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getAdmissionsReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetAdmissionsReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetAdmissionsReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_ADC:ADM./DISCHARGE~;;10;");
        }

        public string getExpandedAdtReport(string fromDate, string toDate, int nrpts)
        {
            return getExpandedAdtReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getExpandedAdtReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetExpandedAdtReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetExpandedAdtReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_EADT:EXPANDED ADT~;;64;");
        }

        public string getDischargesReport(string fromDate, string toDate, int nrpts)
        {
            return getDischargesReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getDischargesReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetDischargesReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetDischargesReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_DC:DISCHARGES~;;8;");
        }

        public string getTransfersReport(string fromDate, string toDate, int nrpts)
        {
            return getTransfersReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getTransfersReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetTransfersReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetTransfersReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_TR:TRANSFERS~;;16;");
        }

        public string getFutureClinicVisitsReport(string fromDate, string toDate, int nrpts)
        {
            return getFutureClinicVisitsReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getFutureClinicVisitsReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetFutureClinicVisitsReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetFutureClinicVisitsReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_CVF:FUTURE CLINIC VISITS~;;9;");
        }

        public string getPastClinicVisitsReport(string fromDate, string toDate, int nrpts)
        {
            return getPastClinicVisitsReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getPastClinicVisitsReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetPastClinicVisitsReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetPastClinicVisitsReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_CVP:PAST CLINIC VISITS~;;14;");
        }

        public string getTreatingSpecialtyReport(string fromDate, string toDate, int nrpts)
        {
            return getTreatingSpecialtyReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getTreatingSpecialtyReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetTreatingSpecialtyReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetTreatingSpecialtyReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_TS:TREATING SPECIALTY~;;17;");
        }

        public string getCareTeamReport()
        {
            return getCareTeamReport(cxn.Pid);
        }

        public string getCareTeamReport(string dfn)
        {
            MdoQuery request = buildGetCareTeamReportRequest(dfn);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetCareTeamReportRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            VistaQuery vq = new VistaQuery("ORWPT1 PCDETAIL");
            vq.addParameter(vq.LITERAL, dfn);
            return vq;
        }

        public string getDischargeDiagnosisReport(string fromDate, string toDate, int nrpts)
        {
            return getDischargeDiagnosisReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getDischargeDiagnosisReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetDischargeDiagnosisReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetDischargeDiagnosisReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_DD:DISCHARGE DIAGNOSIS~;;11;");
        }

        public IcdReport[] getIcdProceduresReport(string fromDate, string toDate, int nrpts)
        {
            return getIcdProceduresReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public IcdReport[] getIcdProceduresReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetIcdProceduresReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return toIcdReports(response);
        }

        internal MdoQuery buildGetIcdProceduresReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_PRC:ICD PROCEDURES~PRC;ORDV07;50;");
        }

        public IcdReport[] getIcdSurgeryReport(string fromDate, string toDate, int nrpts)
        {
            return getIcdSurgeryReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public IcdReport[] getIcdSurgeryReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetIcdSurgeryReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return toIcdReports(response);
        }

        internal MdoQuery buildGetIcdSurgeryReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_OPC:ICD SURGERIES~ICDSUR;ORDV07;12;");
        }

        internal IcdReport[] toIcdReports(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            ArrayList lst = new ArrayList();
            IcdReport rpt = null;
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i] == "")
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                if (flds[0] == "1")
                {
                    if (rpt != null)
                    {
                        lst.Add(rpt);
                    }
                    rpt = new IcdReport();
                    string[] subflds = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                    if (subflds.Length == 2)
                    {
                        rpt.Facility = new SiteId(subflds[1], subflds[0]);
                    }
                    else if (flds[1] != "")
                    {
                        rpt.Facility = new SiteId(cxn.DataSource.SiteId.Id, flds[1]);
                    }
                    else
                    {
                        rpt.Facility = cxn.DataSource.SiteId;
                    }
                }
                else if (flds[0] == "2")
                {
                    rpt.Timestamp = VistaTimestamp.toUtcFromRdv(flds[1]);
                }
                else if (flds[0] == "3")
                {
                    rpt.Title = flds[1];
                }
                else if (flds[0] == "4")
                {
                    rpt.IcdCode = flds[1];
                }
            }
            if (rpt != null)
            {
                lst.Add(rpt);
            }
            return (IcdReport[])lst.ToArray(typeof(IcdReport));
        }

        public string getCompAndPenReport(string fromDate, string toDate, int nrpts)
        {
            return getCompAndPenReport(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getCompAndPenReport(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetCompAndPenReportRequest(dfn, fromDate, toDate, nrpts);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetCompAndPenReportRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_CP:COMP & PEN EXAMS~;;65;");
        }

        #endregion


        #region RAPTOR Radiology Write Back Enhancements

        public String getEncounterTitle(String patientId, String locationIen, String providerDuz)
        {
            MdoQuery request = buildGetEncounterTitleRequest(patientId, locationIen, providerDuz);
            return (String)this.cxn.query(request);
        }

        internal MdoQuery buildGetEncounterTitleRequest(String patientId, String locationIen, String providerDuz)
        {
            VistaQuery vq = new VistaQuery("ORWPT ENCTITL");
            vq.addParameter(vq.LITERAL, patientId);
            vq.addParameter(vq.LITERAL, locationIen);
            vq.addParameter(vq.LITERAL, providerDuz);
            return vq;
        }

        public String getCurrentTreatingSpecialty(String patientId)
        {
            MdoQuery request = buildGetCurrentTreatingSpecialtyRequest(patientId);
            return (String)this.cxn.query(request);
        }

        internal MdoQuery buildGetCurrentTreatingSpecialtyRequest(String patientId)
        {
            VistaQuery vq = new VistaQuery("OREVNTX1 CURSPE");
            vq.addParameter(vq.LITERAL, patientId);
            return vq;
        }

        #endregion





        public PatientCareTeam getPatientCareTeamMembers(string station)
        {
            throw new NotImplementedException();
        }
    }
}

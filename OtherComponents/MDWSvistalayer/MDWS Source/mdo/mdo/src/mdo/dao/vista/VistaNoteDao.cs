using System;
using System.Collections.Generic;
using System.Collections;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaNoteDao : INoteDao
    {
        AbstractConnection cxn = null;

        public VistaNoteDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        #region Note Definitions

        public bool isNoteDefined(string noteDefinitionIen)
        {
            VistaUtils.CheckRpcParams(noteDefinitionIen);
            string arg = "$D(^TIU(8925.1," + noteDefinitionIen + ",0))";
            MdoQuery request = VistaUtils.buildGetVariableValueRequest(arg);
            string response = (string)cxn.query(request);
            return response == "1";
        }

        public Dictionary<string, ArrayList> getNoteTitles(string target, string direction)
        {
            MdoQuery request = buildGetNoteTitlesRequest(target.ToUpper(), direction);
            string response = (string)cxn.query(request);
            return toNoteTitles(response);
        }

        internal MdoQuery buildGetNoteTitlesRequest(string target, string direction)
        {
            VistaQuery vq = new VistaQuery("TIU LONG LIST OF TITLES");
            vq.addParameter(vq.LITERAL, "3");
            if (target == null || target == "")
            {
                vq.addParameter(vq.LITERAL, "");
            }
            else
            {
                vq.addParameter(vq.LITERAL, VistaUtils.adjustForNameSearch(target));
            }
            vq.addParameter(vq.LITERAL, VistaUtils.setDirectionParam(direction));
            return vq;
        }

        internal Dictionary<string, ArrayList> toNoteTitles(string response)
        {
            if (response == "")
            {
                return null;
            }
            String[] lines = StringUtils.split(response, StringUtils.CRLF);
            Dictionary<string, ArrayList> result = new Dictionary<string, ArrayList>(lines.Length);
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i] == "")
                {
                    continue;
                }
                String[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                if (result.ContainsKey(flds[0]))
                {
                    ArrayList lst = result[flds[0]];
                    lst.Add(flds[1]);
                }
                else
                {
                    ArrayList lst = new ArrayList();
                    lst.Add(flds[1]);
                    result.Add(flds[0], lst);
                }
            }
            return result;
        }

        public StringDictionary getEnterpriseTitles()
        {
            return cxn.SystemFileHandler.getLookupTable(VistaConstants.TIU_VHA_ENTERPRISE);
        }

        public string getNoteDefinitionIen(string title)
        {
            if (String.IsNullOrEmpty(title))
            {
                throw new NullOrEmptyParamException("title");
            }
            string arg = "$O(^TIU(8925.1,\"B\",\"" + title + "\",0))";
            string response = VistaUtils.getVariableValue(cxn, arg);
            return VistaUtils.errMsgOrIen(response);
        }

        public NoteDefinition getNationalNoteDefinition(string ien)
        {
            VistaUtils.CheckRpcParams(ien);
            string arg = "$G(^TIU(8926.1," + ien + ",0))_U_$G(^TIU(8926.1," + ien + ",\"VUID\"))";
            string response = VistaUtils.getVariableValue(cxn, arg);
            NoteDefinition result = toNoteDefinition(response);
            result.Id = ien;
            return result;
        }

        internal NoteDefinition toNoteDefinition(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] flds = StringUtils.split(response, StringUtils.CARET);
            NoteDefinition result = new NoteDefinition();
            result.StandardTitle = flds[0];
            result.Vuid = flds[8];
            return result;
        }

        #endregion

        #region Read Progress Notes

        internal Note[] getNotes(string fromDate, string toDate, int nNotes, string noteType)
        {
            return getNotes(cxn.Pid, fromDate, toDate, nNotes, noteType);
        }

        internal Note[] getNotes(string dfn, string fromDate, string toDate, int nNotes, string noteType)
        {
            MdoQuery request = buildGetNotesRequest(dfn, fromDate, toDate, nNotes, noteType);
            string response = (string)cxn.query(request);
            return toNotes(response);
        }

        internal MdoQuery buildGetNotesRequest(string dfn, string fromDate, string toDate, int nNotes, string noteType)
        {
            VistaUtils.CheckRpcParams(dfn, fromDate, toDate);
            VistaQuery vq = new VistaQuery("TIU DOCUMENTS BY CONTEXT");
            vq.addParameter(vq.LITERAL, "3");
            if (noteType == "1")
            {
                if (fromDate == "")
                {
                    fromDate = "-1";
                    toDate = "-1";
                    vq.addParameter(vq.LITERAL, "1");
                }
                else
                {
                    if (toDate == "")
                    {
                        //Changed the call to be -1 and let the Vista Procedure change
                        //it, this way we can test calls with blank/default dates, as the
                        //call signature will be constant (b/w Mock XML Doc and the test 
                        //call)
                        //VistaToolsDao toolsDao = new VistaToolsDao(cxn);
                        //toDate = toolsDao.getTimestamp();
                        toDate = "-1";
                    }
                    vq.addParameter(vq.LITERAL, "5");
                }
            }
            else
            {
                vq.addParameter(vq.LITERAL, noteType);
            }
            vq.addParameter(vq.LITERAL, dfn);

            if (fromDate =="")
                {fromDate = "-1";};
            if (toDate =="")
                {toDate = "-1";};
            if (fromDate == "-1")
                {vq.addParameter(vq.LITERAL, fromDate); }
            else
                {vq.addParameter(vq.LITERAL, VistaTimestamp.fromUtcFromDate(fromDate)); }
            if (toDate == "-1")
                {vq.addParameter(vq.LITERAL,toDate); }
            else
                {vq.addParameter(vq.LITERAL, VistaTimestamp.fromUtcToDate(toDate));}
            vq.addParameter(vq.LITERAL, "0");
            vq.addParameter(vq.LITERAL, nNotes.ToString());
            vq.addParameter(vq.LITERAL, "D");
            vq.addParameter(vq.LITERAL, "1");
            return vq;
        }

        internal Note[] toNotes(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] rex = StringUtils.split(response, StringUtils.CRLF);
            ArrayList lst = new ArrayList();
            for (int i = 0; i < rex.Length; i++)
            {
                if (rex[i] == "" || rex[i].StartsWith("^"))
                {
                    continue;
                }
                string[] flds = StringUtils.split(rex[i], StringUtils.CARET);
                Note note = new Note();
                note.Id = flds[0];
                note.LocalTitle = flds[1];
                note.Timestamp = VistaTimestamp.toUtcString(flds[2]);
                string[] authorFlds = StringUtils.split(flds[4], StringUtils.SEMICOLON);
                if (authorFlds[0] != "0")
                {
                    note.Author = new Author(authorFlds[0], authorFlds[2], authorFlds[1]);
                }
                if (flds.Length > 5)
                {
                    //[DP] 4/6/2011 Fixed the Location ID and Name fields on the 
                    //Location Object on the Note Object.
                    //The location name was being put into the location id field.  
                    //The RPC returns the Name of the Location within the "Site" 
                    //(Hospital), this was putting it into the id field, it now 
                    //puts it into the name field, and gets the id with a get
                    //variable value call to the Vista database.

                    string noteLocationName = flds[5];
                    string locationId = "";
                    if ((noteLocationName != null) & (noteLocationName != ""))
                    {
                        //Get the ID from the Location Name
                        string arg = "$O(^SC(\"B\",\"" + noteLocationName + "\",0))";
                        MdoQuery request = VistaUtils.buildGetVariableValueRequest(arg);
                        locationId = (string)cxn.query(request);
                    }
                        note.Location = new HospitalLocation(locationId, noteLocationName);
                }
                if (flds.Length > 10)
                {
                    note.HasImages = (flds[10] != "0");
                }
                if (flds.Length > 12)
                {
                    note.HasAddendum = (flds[12] == "+");
                }
                if (flds.Length > 13)
                {
                    if (flds[13].Length > 1)
                    {
                        note.OriginalNoteId = flds[13];
                        note.IsAddendum = true;
                    }
                }
                note.SiteId = cxn.DataSource.SiteId;
                lst.Add(note);
            }
            return (Note[])lst.ToArray(typeof(Note));
        }

        public string getNoteText(string noteIEN)
        {
            MdoQuery request = buildGetNoteTextRequest(noteIEN);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetNoteTextRequest(string noteIEN)
        {
            VistaUtils.CheckRpcParams(noteIEN);
            VistaQuery vq = new VistaQuery("TIU GET RECORD TEXT");
            vq.addParameter(vq.LITERAL, noteIEN);
            return vq;
        }

        public Note[] getSignedNotes(String fromDate, String toDate, int nNotes)
        {
            return getNotes(fromDate, toDate, nNotes, "1");
        }

        public Note[] getUnsignedNotes(String fromDate, String toDate, int nNotes)
        {
            return getNotes(fromDate, toDate, nNotes, "2");
        }

        public Note[] getUncosignedNotes(String fromDate, String toDate, int nNotes)
        {
            return getNotes(fromDate, toDate, nNotes, "3");
        }

        public Note[] getNotes(string fromDate, string toDate, int nNotes)
        {
            return getNotes(cxn.Pid, fromDate, toDate, nNotes);
        }

        public Note[] getNotes(string dfn, string fromDate, string toDate, int nNotes)
        {
            MdoQuery request = null;
            string response = "";

            try
            {
                request = buildGetNotesRequest(dfn, fromDate, toDate, nNotes);
                response = (string)cxn.query(request);
                return toNotesFromRdv(response);
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, MdoExceptionCode.REQUEST_RESPONSE_ERROR, exc);
            }
        }

        internal MdoQuery buildGetNotesRequest(string dfn, string fromDate, string toDate, int nNotes)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nNotes, "OR_PN:PROGRESS NOTES~TIUPRG;ORDV04;15;");
        }

        internal Note[] toNotesFromRdv(string response)
        {
            if (String.IsNullOrEmpty(response))
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            ArrayList lst = new ArrayList();
            Note note = null;
            string scannedDoc = "";
            bool fInTextPortion = false;
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i] == "")
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                if (flds.Length == 1 && fInTextPortion)
                {
                    scannedDoc += lines[i] + "\r\n";
                    continue;
                }
                if (flds[1] == "[+]")
                {
                    note.Text = VistaUtils.removeCtlChars(note.Text);
                    lst.Add(note);
                    fInTextPortion = false;
                }
                else if (flds[0] == "1")
                {
                    fInTextPortion = false;
                    note = new Note();
                    string[] subflds = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                    if (subflds.Length == 2)
                    {
                        note.SiteId = new SiteId(subflds[1], subflds[0]);
                    }
                    else if (flds[1] != "")
                    {
                        note.SiteId = new SiteId(cxn.DataSource.SiteId.Id, flds[1]);
                    }
                    else
                    {
                        note.SiteId = cxn.DataSource.SiteId;
                    }
                }
                else if (flds[0] == "2")
                {
                    note.Id = flds[1];
                }
                else if (flds[0] == "3")
                {
                    note.Timestamp = VistaTimestamp.toUtcFromRdv(flds[1]);
                }
                else if (flds[0] == "4")
                {
                    note.LocalTitle = flds[1];
                }
                else if (flds[0] == "5")
                {
                    note.Author = new Author("", flds[1], "");
                }
                else if (flds[0] == "6")
                {
                    fInTextPortion = true;
                    if (!String.IsNullOrEmpty(scannedDoc))
                    {
                        note.Text += scannedDoc + "\r\n";
                        scannedDoc = "";
                    }
                    note.Text += flds[1] + "\r\n";
                    if (flds[1].StartsWith("STANDARD TITLE"))
                    {
                        string[] parts = StringUtils.split(flds[1], StringUtils.COLON);
                        note.StandardTitle = parts[1].Trim();
                    }
                }
            }
            Note[] notes = (Note[])lst.ToArray(typeof(Note));
            return notes;
        }

        internal void addEnterpriseTitles(Note[] notes)
        {
            StringDictionary enterpriseTitles = getEnterpriseTitles();
            for (int i = 0; i < notes.Length; i++)
            {
                if (notes[i].DocumentDefinitionId != null &&
                    enterpriseTitles.ContainsKey(notes[i].DocumentDefinitionId))
                {
                    notes[i].StandardTitle = enterpriseTitles[notes[i].DocumentDefinitionId];
                }
            }
        }

        #endregion

        #region Discharge Summaries

        public Note[] getDischargeSummaries(string fromDate, string toDate, int nNotes)
        {
            return getDischargeSummaries(cxn.Pid, fromDate, toDate, nNotes);
        }

        public Note[] getDischargeSummaries(string dfn, string fromDate, string toDate, int nNotes)
        {
            MdoQuery request = buildGetDischargeSummariesRequest(dfn, fromDate, toDate, nNotes);
            string response = StringUtils.stripInvalidXmlCharacters((string)cxn.query(request));
            //string response = (string)cxn.query(request);
            return toNotesFromDischargeSummaries(response);
        }

        internal MdoQuery buildGetDischargeSummariesRequest(string dfn, string fromDate, string toDate, int nNotes)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nNotes, "OR_DS:DISCHARGE SUMMARY~TIUDCS;ORDV04;57;");
        }

        internal Note[] toNotesFromDischargeSummaries(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            ArrayList lst = new ArrayList();
            Note note = null;
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i] == "")
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                if (flds[1] == "[+]")
                {
                    lst.Add(note);
                }
                else if (flds[0] == "1")
                {
                    note = new Note();
                    note.StandardTitle = "DISCHARGE SUMMARY";
                    note.LocalTitle = "Discharge Summary";
                    note.Text = VistaUtils.removeCtlChars(note.Text);
                    string[] subflds = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                    if (subflds.Length == 2)
                    {
                        note.SiteId = new SiteId(subflds[1], subflds[0]);
                    }
                    else if (flds[1] != "")
                    {
                        note.SiteId = new SiteId(cxn.DataSource.SiteId.Id, flds[1]);
                    }
                    else
                    {
                        note.SiteId = cxn.DataSource.SiteId;
                    }
                }
                else if (flds[0] == "2")
                {
                    note.AdmitTimestamp = VistaTimestamp.toUtcFromRdv(flds[1]);
                }
                else if (flds[0] == "3")
                {
                    note.DischargeTimestamp = VistaTimestamp.toUtcFromRdv(flds[1]);
                }
                else if (flds[0] == "4")
                {
                    note.Author = new Author("", flds[1], "");
                }
                else if (flds[0] == "5")
                {
                    note.ApprovedBy = new Author("", flds[1], "");
                }
                else if (flds[0] == "6")
                {
                    note.Status = flds[1];
                }
                else if (flds[0] == "7")
                {
                    note.Text += flds[1] + "\r\n";
                }
            }
            Note[] notes = (Note[])lst.ToArray(typeof(Note));
            return notes;
        }

        #endregion

        #region Other Notes

        public string getCrisisNotes(string fromDate, string toDate, int nrpts)
        {
            return getCrisisNotes(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getCrisisNotes(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetCrisisNotesRequest(dfn, fromDate, toDate, nrpts);
            return (string)cxn.query(request);
        }

        public MdoQuery buildGetCrisisNotesRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_CN:CRISIS NOTES~;;5;");
        }

        public string getAdvanceDirectives(string fromDate, string toDate, int nrpts)
        {
            return getAdvanceDirectives(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getAdvanceDirectives(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetAdvanceDirectivesRequest(dfn, fromDate, toDate, nrpts);
            return (string)cxn.query(request);
        }

        public MdoQuery buildGetAdvanceDirectivesRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_CD:ADVANCE DIRECTIVE~;;25;");
        }

        public string getClinicalWarnings(string fromDate, string toDate, int nrpts)
        {
            return getClinicalWarnings(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getClinicalWarnings(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = buildGetClinicalWarningsRequest(dfn, fromDate, toDate, nrpts);
            return (string)cxn.query(request);
        }

        public MdoQuery buildGetClinicalWarningsRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_CW:CLINICAL WARNINGS~;;4;");
        }

        public PatientRecordFlagNote[] getPatientRecordFlagLinkedNotes(string pid, string noteDefTitle, string titleIEN)
        {
            MdoQuery request = buildGetPrfLinkedNotes(pid, noteDefTitle, titleIEN);
            string response = (string)cxn.query(request);
            return toPrfNotes(response, pid);
        }

        internal MdoQuery buildGetPrfLinkedNotes(string pid, string noteDefTitle, string titleIEN)
        {
            VistaUtils.CheckRpcParams(pid);
            VistaUtils.CheckRpcParams(noteDefTitle);
            VistaUtils.CheckRpcParams(titleIEN);

            VistaQuery vq = new VistaQuery("TIU GET LINKED PRF NOTES");
            vq.addParameter(vq.LITERAL, pid);
            vq.addParameter(vq.LITERAL, noteDefTitle);
            vq.addParameter(vq.LITERAL, titleIEN);
            return vq;
        }

        internal PatientRecordFlagNote[] toPrfNotes(string response, string noteIen)
        {
            if (StringUtils.isEmpty(response))
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            PatientRecordFlagNote[] result = new PatientRecordFlagNote[lines.Length];
            for (int i = 0; i < lines.Length; i++)
            {
                result[i] = toPrfNote(lines[i]);
                //result[i].NoteIen = noteIen;
            }
            return result;
        }

        internal PatientRecordFlagNote toPrfNote(string s)
        {
            string[] flds = StringUtils.split(s, StringUtils.CARET);
            if (flds.Length < 4)
            {
                return null;
            }
            PatientRecordFlagNote result = new PatientRecordFlagNote();

            result.NoteIen = flds[0];
            result.ActionName = flds[1];
            result.ActionTimestamp = flds[2];
            result.DoctorName = flds[3];
            return result;
        }

        public PatientRecordFlag[] getPrfNoteActions(string ien)
        {
            return getPrfNoteActions(ien, cxn.Pid);
        }

        public PatientRecordFlag[] getPrfNoteActions(string ien, string dfn)
        {
            MdoQuery request = buildGetPrfNoteActions(ien, dfn);
            string response = (string)cxn.query(request);
            return toPrfs(response, ien);
        }

        internal MdoQuery buildGetPrfNoteActions(string ien, string dfn)
        {
            VistaUtils.CheckRpcParams(ien);
            VistaUtils.CheckRpcParams(dfn);
            VistaQuery vq = new VistaQuery("TIU GET PRF ACTIONS");
            vq.addParameter(vq.LITERAL, ien);
            vq.addParameter(vq.LITERAL, dfn);
            return vq;
        }

        internal PatientRecordFlag[] toPrfs(string response, string noteIen)
        {
            if (StringUtils.isEmpty(response))
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            PatientRecordFlag[] result = new PatientRecordFlag[lines.Length];
            for (int i = 0; i < lines.Length; i++)
            {
                result[i] = toPrf(lines[i]);
                result[i].NoteId = noteIen;
            }
            return result;
        }

        internal PatientRecordFlag toPrf(string s)
        {
            string[] flds = StringUtils.split(s, StringUtils.CARET);
            if (flds.Length < 5)
            {
                return null;
            }
            PatientRecordFlag result = new PatientRecordFlag();
            result.Id = flds[1];
            result.Name = flds[0];
            result.ActionId = flds[3];
            result.ActionName = flds[2];
            result.ActionTimestamp = VistaTimestamp.toUtcString(flds[4]);
            return result;
        }

        #endregion

        #region Write Note

        public bool isConsultNote(string ien)
        {
            MdoQuery request = buildIsConsultNoteRequest(ien);
            string response = (string)cxn.query(request);
            return (response == "1");
        }

        internal MdoQuery buildIsConsultNoteRequest(string ien)
        {
            VistaUtils.CheckRpcParams(ien);
            VistaQuery vq = new VistaQuery("TIU IS THIS A CONSULT?");
            vq.addParameter(vq.LITERAL, ien);
            return vq;
        }

        public bool isSurgeryNote(string ien)
        {
            MdoQuery request = buildIsSurgeryNoteRequest(ien);
            string response = (string)cxn.query(request);
            return (response == "1");
        }

        internal MdoQuery buildIsSurgeryNoteRequest(string ien)
        {
            VistaUtils.CheckRpcParams(ien);
            VistaQuery vq = new VistaQuery("TIU IS THIS A SURGERY?");
            vq.addParameter(vq.LITERAL, ien);
            return vq;
        }

        public bool isPrfNote(string ien)
        {
            MdoQuery request = buildIsPrfNoteRequest(ien);
            string response = (string)cxn.query(request);
            return (response == "1");
        }

        internal MdoQuery buildIsPrfNoteRequest(string ien)
        {
            VistaUtils.CheckRpcParams(ien);
            VistaQuery vq = new VistaQuery("TIU ISPRF");
            vq.addParameter(vq.LITERAL, ien);
            return vq;
        }

        public string getNotePrintName(string ien)
        {
            MdoQuery request = buildGetNotePrintNameRequest(ien);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetNotePrintNameRequest(string ien)
        {
            VistaUtils.CheckRpcParams(ien);
            VistaQuery vq = new VistaQuery("TIU GET PRINT NAME");
            vq.addParameter(vq.LITERAL, ien);
            return vq;
        }

        //  Why is this not in VistaConsultsDao?
        public Consult[] getConsultRequestsForPatient(string dfn)
        {
            MdoQuery request = buildGetConsultRequestsForPatientRequest(dfn);
            string response = (string)cxn.query(request);
            return toConsultsFromConsultRequestsForPatient(response);
        }

        internal MdoQuery buildGetConsultRequestsForPatientRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            VistaQuery vq = new VistaQuery("GMRC LIST CONSULT REQUESTS");
            vq.addParameter(vq.LITERAL, dfn);
            return vq;
        }

        internal Consult[] toConsultsFromConsultRequestsForPatient(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] rex = StringUtils.split(response, StringUtils.CRLF);
            rex = StringUtils.trimArray(rex);
            int nrex = Convert.ToInt32(rex[0]);
            Consult[] result = new Consult[nrex];
            for (int rsltIdx = 0, rexIdx = 1; rexIdx < rex.Length; rsltIdx++, rexIdx++)
            {
                string[] flds = StringUtils.split(rex[rexIdx], StringUtils.CARET);
                Consult c = new Consult();
                c.Id = flds[0];
                c.Timestamp = VistaTimestamp.toDateTime(flds[1]);
                c.Title = flds[2];
                c.RequestedProcedure = flds[3];
                c.Status = flds[4];
                //c.Service = new KeyValuePair<string, string>("", flds[2]);
                result[rsltIdx] = c;
            }
            return result;
        }

        public bool checkText(string noteIen)
        {
            MdoQuery request = buildCheckTextRequest(noteIen);
            string response = (string)cxn.query(request);
            return (response == "1");
        }

        internal MdoQuery buildCheckTextRequest(string noteIen)
        {
            VistaUtils.CheckRpcParams(noteIen);
            VistaQuery vq = new VistaQuery("ORWTIU CHKTXT");
            vq.addParameter(vq.LITERAL, noteIen);
            return vq;
        }

        public string wasNoteSaved(string noteIen)
        {
            MdoQuery request = buildWasNoteSavedRequest(noteIen);
            string response = (string)cxn.query(request);
            return (response == "1" ? "OK" : StringUtils.piece(response,StringUtils.CARET,2));
        }

        internal MdoQuery buildWasNoteSavedRequest(string noteIen)
        {
            VistaUtils.CheckRpcParams(noteIen);
            VistaQuery vq = new VistaQuery("TIU WAS THIS SAVED?");
            vq.addParameter(vq.LITERAL, noteIen);
            return vq;
        }

        public string whichSignatureAction(string noteIen)
        {
            MdoQuery request = buildWhichSignatureActionRequest(noteIen);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildWhichSignatureActionRequest(string noteIen)
        {
            VistaUtils.CheckRpcParams(noteIen);
            VistaQuery vq = new VistaQuery("TIU WHICH SIGNATURE ACTION");
            vq.addParameter(vq.LITERAL, noteIen);
            return vq;
        }

        public string isUserAuthorized(string noteIen, string permission)
        {
            MdoQuery request = buildIsUserAuthorizedRequest(noteIen, permission);
            string response = (string)cxn.query(request);
            return (response == "1" ? "OK" : StringUtils.piece(response,StringUtils.CARET,2));
        }

        internal MdoQuery buildIsUserAuthorizedRequest(string noteIen, string permission)
        {
            VistaUtils.CheckRpcParams(noteIen);
            if (String.IsNullOrEmpty(permission))
            {
                throw new NullOrEmptyParamException("permission");
            }
            VistaQuery vq = new VistaQuery("TIU AUTHORIZATION");
            vq.addParameter(vq.LITERAL, noteIen);
            vq.addParameter(vq.LITERAL, permission);
            return vq;
        }

        public bool hasAuthorSignedNote(string noteIen, string duz)
        {
            MdoQuery request = buildAuthorSignedNoteRequest(noteIen, duz);
            string response = (string)cxn.query(request);
            return (response == "1");
        }

        internal MdoQuery buildAuthorSignedNoteRequest(string noteIen, string duz)
        {
            VistaUtils.CheckRpcParams(noteIen);
            if (!VistaUtils.isWellFormedDuz(duz))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid DUZ");
            }
            VistaQuery vq = new VistaQuery("TIU HAS AUTHOR SIGNED?");
            vq.addParameter(vq.LITERAL, noteIen);
            vq.addParameter(vq.LITERAL, duz);
            return vq;
        }

        internal string signNote(string noteIen, string esig)
        {
            MdoQuery request = buildSignNoteRequest(noteIen, esig);
            string response = (string)cxn.query(request);
            return (response == "0" ? "OK" : StringUtils.piece(response,StringUtils.CARET,2));
        }

        internal MdoQuery buildSignNoteRequest(string noteIen, string esig)
        {
            VistaUtils.CheckRpcParams(noteIen);
            if (String.IsNullOrEmpty(esig))
            {
                throw new NullOrEmptyParamException("esig");
            }
            VistaQuery vq = new VistaQuery("TIU SIGN RECORD");
            vq.addParameter(vq.LITERAL, noteIen);

            //DP 5/23/2011  Added guard clause to this query builder methods so it will not save an
            //encrypted string in the mock connection file.

            //Console.WriteLine("VistaNoteDao  buildSignNoteRequest " + esig);
            if (cxn.GetType().Name != "MockConnection")
            {
                vq.addEncryptedParameter(vq.LITERAL, esig);
            }
            else
            {
                vq.addParameter(vq.LITERAL, esig);
            }
            //vq.addParameter(vq.LITERAL, esig);
            
            return vq;
        }

        internal KeyValuePair<int, string> toSignedNoteResponse(string response)
        {
            if (response == "0")
            {
                return new KeyValuePair<int, string>(0, "");
            }
            else
            {
                string[] flds = StringUtils.split(response, StringUtils.CARET);
                return new KeyValuePair<int, string>(Convert.ToInt32(flds[0]), flds[1].Trim());
            }
        }

        internal bool userHasUnresolvedConsults(string dfn)
        {
            MdoQuery request = buildUserHasUnresolvedConsultsRequest(dfn);
            string response = (string)cxn.query(request);
            return (StringUtils.piece(response,StringUtils.CARET,1) == "1");
        }

        internal MdoQuery buildUserHasUnresolvedConsultsRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            VistaQuery vq = new VistaQuery("ORQQCN UNRESOLVED");
            vq.addParameter(vq.LITERAL, dfn);
            return vq;
        }

        public bool isOneVisitNote(string docDefId, string dfn, Encounter encounter)
        {
            return isOneVisitNote(docDefId,dfn,VistaUtils.getVisitString(encounter));
        }

        public bool isOneVisitNote(string docDefId, string dfn, string visitStr)
        {
            MdoQuery request = buildIsOneVisitNoteRequest(docDefId, dfn, visitStr);
            string response = (string)cxn.query(request);
            return (response == "1");
        }

        internal MdoQuery buildIsOneVisitNoteRequest(string docDefId, string dfn, string visitStr)
        {
            VistaUtils.CheckRpcParams(docDefId);
            VistaUtils.CheckRpcParams(dfn);
            VistaUtils.CheckVisitString(visitStr);
            VistaQuery vq = new VistaQuery("TIU ONE VISIT NOTE?");
            vq.addParameter(vq.LITERAL, docDefId);
            vq.addParameter(vq.LITERAL, dfn);
            vq.addParameter(vq.LITERAL, visitStr);
            return vq;
        }

        internal bool patch175Installed()
        {
            return cxn.hasPatch("TIU*1.0*175");
        } 

        public string getNoteEncounterString(string noteIEN)
        {
            MdoQuery request = buildGetNoteEncounterStringRequest(noteIEN);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetNoteEncounterStringRequest(string noteIEN)
        {
            VistaUtils.CheckRpcParams(noteIEN);
            VistaQuery vq = new VistaQuery("ORWPCE NOTEVSTR");
            vq.addParameter(vq.LITERAL, noteIEN);
            return vq;
        }

        public string buildInpatientNoteEncounterString(Patient patient)
        {
            if (patient.Location == null || !VistaUtils.isWellFormedIen(patient.Location.Id))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Missing patient location data");
            }
            if (!DateUtils.isWellFormedUtcDateTime(patient.AdmitTimestamp))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Missing admit timestamp");
            }
            return patient.Location.Id + ';' + 
                VistaTimestamp.fromUtcString(patient.AdmitTimestamp) + ";H";
        }

        internal bool userRequiresCosignature(string duz)
        {
            return userRequiresCosignatureForNote(duz, "3");
        }

        internal bool userRequiresCosignatureForNote(string duz, string noteDefinitionIen)
        {
            MdoQuery request = buildRequiresCosignatureRequest(duz, noteDefinitionIen);
            string response = (string)cxn.query(request);
            return (response == "1");
        }

        internal MdoQuery buildRequiresCosignatureRequest(string duz, string noteIEN)
        {
            VistaUtils.CheckRpcParams(duz);
            VistaUtils.CheckRpcParams(noteIEN);
            VistaQuery vq = new VistaQuery("TIU REQUIRES COSIGNATURE");
            vq.addParameter(vq.LITERAL, noteIEN);
            vq.addParameter(vq.LITERAL, "0");
            vq.addParameter(vq.LITERAL, duz);
            return vq;
        }

        /// <summary>
        /// Checks to see if this user needs a cosigner for this note.
        /// </summary>
        /// <param name="userDuz"></param>
        /// <param name="noteDefinitionIen"></param>
        /// <param name="authorDuz"></param>
        /// <returns></returns>
        public bool isCosignerRequired(string userDuz, string noteDefinitionIen, string authorDuz = null)
        {
            if (userRequiresCosignature(userDuz))
            {
                return true;
            }
            if (userRequiresCosignatureForNote(userDuz, noteDefinitionIen))
            {
                return true;
            }
            if (String.IsNullOrEmpty(authorDuz))
            {
                return false;
            }
            if (userRequiresCosignature(authorDuz))
            {
                return true;
            }
            if (userRequiresCosignatureForNote(authorDuz, noteDefinitionIen))
            {
                return true;
            }

            return false;
        }

        internal string okToCreateNote(Note note, string dfn, Encounter encounter)
        {
            if (isSurgeryNote(note.DocumentDefinitionId))
            {
                return "Cannot create new surgery note at this time";
            }

            if (isOneVisitNote(note.DocumentDefinitionId, dfn, VistaUtils.getVisitString(encounter)))
            {
                string msg = "There is already a " + note.LocalTitle + " note for this visit.  \r\n" +
                    "Only ONE record of this type per Visit is allowed...\r\n\r\n" +
                    "You can addend the existing record.";
                return msg;
            }

            if (isConsultNote(note.DocumentDefinitionId) && String.IsNullOrEmpty(note.ConsultId))
            {
                return "Missing consult IEN";
            }

            if (!note.IsAddendum)
            {
                if (isPrfNote(note.DocumentDefinitionId) && String.IsNullOrEmpty(note.PrfId))
                {
                    return "Missing PRF IEN";
                }

                bool fCosigner = false;
                if (note.Author.Id == cxn.Uid)
                {
                    fCosigner = isCosignerRequired(cxn.Uid, note.DocumentDefinitionId);
                }
                else
                {
                    fCosigner = isCosignerRequired(cxn.Uid, note.DocumentDefinitionId, note.Author.Id);
                }
                if (fCosigner && (note.Cosigner == null || String.IsNullOrEmpty(note.Cosigner.Id)))
                {
                    return "Missing cosigner";
                }
            }
            return "OK";
        }

        public string createNote(Encounter encounter, Note note)
        {
            return createNote(cxn.Pid, encounter, note);
        }

        internal string createNote(string dfn, Encounter encounter, Note note)
        {
            MdoQuery request = buildCreateNoteRequest(dfn, encounter, note);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildCreateNoteRequest(string dfn, Encounter encounter, Note note)
        {
            VistaQuery vq = new VistaQuery("TIU CREATE RECORD");
            vq.addParameter(vq.LITERAL, dfn);
            vq.addParameter(vq.LITERAL, note.DocumentDefinitionId);

            //VistA documentation says it will accept an encounter timestamp here, but
            //CPRS hard codes a blank
            vq.addParameter(vq.LITERAL, "");

            //VistA documentation says encounter location IEN next, but CPRS hard codes a blank
            vq.addParameter(vq.LITERAL, "");

            //VistA documentation says encounter IEN next, but CPRS hard codes a blank
            vq.addParameter(vq.LITERAL, "");   

            string vistaTS = VistaTimestamp.Now;
            DictionaryHashList lst = new DictionaryHashList();
            lst.Add("1202", note.Author.Id);
            lst.Add("1301", note.Timestamp);
            lst.Add("1205", encounter.LocationId);
            if (note.Cosigner != null)
            {
                lst.Add("1208", note.Cosigner.Id);
            }
            if (note.ConsultId != "")
            {
                lst.Add("1405", note.ConsultId + ";GMR(123,");
            }
            string subject = (note.Subject == null) ? "" : note.Subject;
            if (subject.Length > 80)
            {
                subject = subject.Substring(0,80);
            }
            lst.Add("1701", subject);
            if (!StringUtils.isEmpty(note.ParentId))
            {
                lst.Add("2101", note.ParentId);
            }
            vq.addParameter(vq.LIST, lst);

            string timestamp = VistaTimestamp.fromUtcString(encounter.Timestamp);
            timestamp = VistaTimestamp.trimZeroes(timestamp);
            string visitStr = encounter.LocationId + ';' + timestamp + ';' + encounter.Type;
            vq.addParameter(vq.LITERAL, visitStr);

            //Vista documentation says it accepts 1 or 0, but 
            //CPRS hard codes a 1 to suppress commit logic
            vq.addParameter(vq.LITERAL, "1");

            //Vista documentation adds this param, but CPRS omits it
            //if (noAsave)
            //{
            //    vq.addParameter(vq.LITERAL, "1");
            //}
            return vq;
        }

        internal string updateNote(Note note)
        {
            return updateNote(cxn.Pid, note);
        }

        internal string updateNote(string dfn, Note note)
        {
            MdoQuery request = buildUpdateNoteRequest(dfn, note);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildUpdateNoteRequest(string dfn, Note note)
        {
            VistaQuery vq = new VistaQuery("TIU UPDATE RECORD");
            vq.addParameter(vq.LITERAL, note.Id);

            DictionaryHashList lst = new DictionaryHashList();
            lst.Add(".01", note.DocumentDefinitionId);
            lst.Add("1202", note.Author.Id);
            if (note.Cosigner != null)
            {
                lst.Add("1208", note.Cosigner.Id);
            }
            if (!StringUtils.isEmpty(note.ConsultId))
            {
                lst.Add("1405", note.ConsultId + ";GMR(123,");
            }
            lst.Add("1301", note.Timestamp);

            string subject = (note.Subject == null) ? "" : note.Subject;
            if (subject.Length > 80)
            {
                subject = subject.Substring(0, 80);
            }
            lst.Add("1701", subject);
            if (!StringUtils.isEmpty(note.ParentId))
            {
                lst.Add("2101", note.ParentId);
            }
            if (!StringUtils.isEmpty(note.ProcId))
            {
                lst.Add("70201", note.ProcId);
            }
            if (!StringUtils.isEmpty(note.ProcTimestamp))
            {
                lst.Add("70202", VistaTimestamp.fromUtcString(note.ProcTimestamp));
            }
            vq.addParameter(vq.LIST, lst);

            return vq;
        }

        internal string lockNote(string noteIEN)
        {
            MdoQuery request = buildLockNoteRequest(noteIEN);
            string response = (string)cxn.query(request);
            return VistaUtils.errMsgOrZero(response);
        }

        internal MdoQuery buildLockNoteRequest(string noteIEN)
        {
            VistaQuery vq = new VistaQuery("TIU LOCK RECORD");
            vq.addParameter(vq.LITERAL, noteIEN);
            return vq;
        }

        internal void unlockNote(string noteIEN)
        {
            MdoQuery request = buildUnlockNoteRequest(noteIEN);
            string response = (string)cxn.query(request);
        }

        internal MdoQuery buildUnlockNoteRequest(string noteIEN)
        {
            VistaQuery vq = new VistaQuery("TIU UNLOCK RECORD");
            vq.addParameter(vq.LITERAL, noteIEN);
            return vq;
        }

        internal ArrayList toLines(string text)
        {
            int maxCharsPerLine = 80;
            ArrayList lineLst = new ArrayList();
            string[] lines = StringUtils.split(text, "|");
            for (int i = 0; i < lines.Length; i++)
            {
                string thisLine = lines[i];
                if (thisLine == "")
                {
                    lineLst.Add(thisLine);
                    continue;
                }
                while (thisLine.Length > maxCharsPerLine)
                {
                    int idx = StringUtils.getFirstWhiteSpaceAfter(thisLine, maxCharsPerLine);
                    if (idx == -1)
                    {
                        idx = maxCharsPerLine;
                    }
                    lineLst.Add(thisLine.Substring(0, idx));
                    while (StringUtils.isWhiteSpace(thisLine[idx]))
                    {
                        idx++;
                    }
                    thisLine = thisLine.Substring(idx);
                }
                if (thisLine.Length > 0)
                {
                    lineLst.Add(thisLine);
                }
            }
            return lineLst;
        }

        internal NoteResult setNoteText(string noteIEN, string text, bool fSuppressCommit)
        {
            int maxCharsPerLine = 80;
            ArrayList lineLst = new ArrayList();
            string[] lines = StringUtils.split(text, "|");
            for (int i = 0; i < lines.Length; i++)
            {
                string thisLine = lines[i].TrimEnd();
                if (thisLine == "")
                {
                    lineLst.Add(thisLine);
                    continue;
                }
                while (thisLine.Length > maxCharsPerLine)
                {
                    int idx = StringUtils.getFirstWhiteSpaceAfter(thisLine, maxCharsPerLine);
                    if (idx == -1)
                    {
                        idx = maxCharsPerLine;
                    }
                    lineLst.Add(thisLine.Substring(0, idx));
                    while (idx < thisLine.Length && StringUtils.isWhiteSpace(thisLine[idx]))
                    {
                        idx++;
                    }
                    thisLine = thisLine.Substring(idx);
                }
                if (thisLine.Length > 0)
                {
                    lineLst.Add(thisLine);
                }
            }
            int npages = (lineLst.Count / 300) + 1;
            int nlinesPerPage = 0;
            int pagenum = 1;
            string key = "";
            DictionaryHashList argList = new DictionaryHashList();
            for (int linenum = 0; linenum < lineLst.Count; linenum++)
            {
                key = "\"TEXT\"," + (linenum + 1) + ",0";
                string value = StringUtils.filteredString((string)lineLst[linenum]);
                argList.Add(key, value);
                nlinesPerPage++;
                if (nlinesPerPage == 300)
                {
                    argList.Add("\"HDR\"", pagenum.ToString() + '^' + npages.ToString() + '^');
                    string rtn = writeText("TIU SET DOCUMENT TEXT", noteIEN, argList, fSuppressCommit);
                    rtn = resultOK(rtn, noteIEN, pagenum, npages);
                    if (rtn != "OK")
                    {
                        throw new Exception(rtn);
                    }
                    pagenum++;
                    nlinesPerPage = 0;
                }
            }
            if (nlinesPerPage > 0)
            {
                argList.Add("\"HDR\"", pagenum.ToString() + '^' + npages.ToString());
                string rtn = writeText("TIU SET DOCUMENT TEXT", noteIEN, argList, fSuppressCommit);
                rtn = resultOK(rtn, noteIEN, pagenum, npages);
                if (rtn != "OK")
                {
                    throw new Exception(rtn);
                }
                pagenum++;
            }
            return new NoteResult(noteIEN, npages, pagenum);
        }

        internal string writeText(string rpcName, string noteIen, DictionaryHashList lst, bool fSuppressCommit)
        {
            MdoQuery request = buildWriteTextRequest(rpcName, noteIen, lst, fSuppressCommit);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildWriteTextRequest(string rpcName, string noteIEN, DictionaryHashList lst, bool fSuppressCommit)
        {
            VistaQuery vq = new VistaQuery(rpcName);
            vq.addParameter(vq.LITERAL, noteIEN);
            vq.addParameter(vq.LIST, lst);
            vq.addParameter(vq.LITERAL, "0");
            return vq;
        }

        internal string resultOK(string s, string noteIEN, int lastPageSent, int totalPages)
        {
            try
            {
                string[] flds = StringUtils.split(s, StringUtils.CARET);
                if (flds[0] == "0")
                {
                    return flds[3];
                }
                if (flds[0] != noteIEN)
                {
                    return "IEN mismatch";
                }
                if (Convert.ToInt16(flds[1]) != lastPageSent)
                {
                    return "Last page received not last page sent";
                }
                if (Convert.ToInt16(flds[2]) != totalPages)
                {
                    return "Total pages mismatch";
                }
            }
            catch (Exception ex)
            {
                return ex.Message;
            }
            return "OK";
        }

        public NoteResult writeNote(
            string noteDefinitionIen,
            Encounter encounter,
            string text,
            string authorDUZ,
            string cosignerDUZ = null,
            string consultIEN = null,
            string prfIEN = null)
        {
            return writeNote(cxn.Pid, noteDefinitionIen, encounter, text, authorDUZ, cosignerDUZ, consultIEN, prfIEN);
        }
        
        public NoteResult writeNote(
            string dfn,
            string noteDefinitionIen,
            Encounter encounter,
            string text,
            string authorDUZ,
            string cosignerDUZ = null,
            string consultIEN = null,
            string prfIEN = null)
        {
            VistaUtils.CheckRpcParams(dfn);
            VistaUtils.CheckRpcParams(noteDefinitionIen);
            if (encounter == null)
            {
                throw new NullOrEmptyParamException("encounter");
            }
            if (String.IsNullOrEmpty(text))
            {
                throw new NullOrEmptyParamException("text");
            }
            if (!String.IsNullOrEmpty(consultIEN))
            {
                VistaUtils.CheckRpcParams(consultIEN);
            }
            if (!String.IsNullOrEmpty(prfIEN))
            {
                VistaUtils.CheckRpcParams(prfIEN);
            }

            Note note = new Note();
            note.DocumentDefinitionId = noteDefinitionIen;
            note.Author = new Author(authorDUZ,"","");
            VistaToolsDao toolsDao = new VistaToolsDao(cxn);
            note.Timestamp = VistaTimestamp.fromUtcString(toolsDao.getTimestamp());
            note.ConsultId = consultIEN;
            note.IsAddendum = false;
            note.PrfId = prfIEN;
            if (!String.IsNullOrEmpty(cosignerDUZ))
            {
                VistaUtils.CheckRpcParams(cosignerDUZ);
                note.Cosigner = new Author(cosignerDUZ, "", "");
            }
            note.Text = text;

            string s = okToCreateNote(note, dfn, encounter);
            if (s != "OK")
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, s);
            }

            if (!String.IsNullOrEmpty(consultIEN))
            {
                VistaConsultDao consultDao = new VistaConsultDao(cxn);
                string orderIEN = consultDao.getOrderNumberForConsult(consultIEN);
                if (orderIEN == "")
                {
                    // Do nothing - CPRS assumes it's an Interfacility Consult
                }
                else
                {
                    VistaOrdersDao orderDao = new VistaOrdersDao(cxn);
                    s = orderDao.lockOrder(orderIEN);
                    if (s != "OK")
                    {
                        throw new RecordLockingException("Unable to lock order: " + s);
                    }
                }
            }

            string noteIEN = createNote(dfn, encounter, note);
            if (noteIEN.StartsWith("0^"))
            {
                throw new MdoException(MdoExceptionCode.VISTA_FAULT, StringUtils.piece(noteIEN,StringUtils.CARET,2));
            }
            s = lockNote(noteIEN);
            if (s != "OK")
            {
                throw new RecordLockingException("Unable to lock note: " + s);
            }
            note.Id = noteIEN;

            s = updateNote(dfn, note);
            if (s != noteIEN)
            {
                throw new MdoException(MdoExceptionCode.VISTA_FAULT, "Error updating note " + noteIEN + ": expected IEN, got " + s);
            }

            NoteResult result = null;
            try
            {
                result = setNoteText(noteIEN, text, false);
            }
            catch (IndexOutOfRangeException ie)
            {
                string msg = "setNoteText: " + ie.Message + "\r\n" + ie.StackTrace;
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, msg);
            }
                
            if (!String.IsNullOrEmpty(prfIEN))
            {
                //Handle PRF
                PatientRecordFlag[] flags = getPrfNoteActions(noteIEN);
                PatientRecordFlag thisFlag = getFlag(flags, prfIEN);
                if (thisFlag == null)
                {
                    throw new MdoException(MdoExceptionCode.DATA_INVALID, "Unable to link note " + noteIEN + " to PRF: invalid IEN (" + prfIEN + ")");
                }
                s = linkNoteToFlag(noteIEN, thisFlag.Id, thisFlag.ActionId);
                if (s != "OK")
                {
                    throw new MdoException(MdoExceptionCode.VISTA_FAULT, "Unable to link note " + noteIEN + " to PRF " + prfIEN);
                }
            }

            return result;
        }

        internal PatientRecordFlag getFlag(PatientRecordFlag[] flags, string prfIEN)
        {
            for (int i = 0; i < flags.Length; i++)
            {
                if (flags[i].Id == prfIEN)
                {
                    return flags[i];
                }
            }
            return null;
        }

        internal string linkNoteToFlag(string noteIEN, string flagIEN, string actionIEN)
        {
            MdoQuery request = buildLinkNoteToFlagRequest(noteIEN, flagIEN, actionIEN);
            string response = (string)cxn.query(request);
            return (response == "1" ? "OK" : StringUtils.piece(response,StringUtils.CARET,2));
        }

        internal MdoQuery buildLinkNoteToFlagRequest(string noteIEN, string flagIEN, string actionIEN)
        {
            VistaQuery vq = new VistaQuery();
            vq.addParameter(vq.LITERAL, noteIEN);
            vq.addParameter(vq.LITERAL, flagIEN);
            vq.addParameter(vq.LITERAL, actionIEN);
            vq.addParameter(vq.LITERAL, cxn.Pid);
            return vq;
        }

        public string signNote(string noteIEN, string DUZ, string esig)
        {
            VistaUtils.CheckRpcParams(noteIEN);
            VistaUtils.CheckRpcParams(DUZ);
            if (String.IsNullOrEmpty(esig))
            {
                throw new NullOrEmptyParamException("esig");
            }

            if (!checkText(noteIEN))
            {
                return "Note contains no text";
            }
            string s = wasNoteSaved(noteIEN);
            if (s != "OK")
            {
                return s;
            }
            string permission = whichSignatureAction(noteIEN);
            if (permission != "SIGNATURE")
            {
                return "Invalid signature action: expected SIGNATURE, got " + permission;
            }
            s = isUserAuthorized(noteIEN, permission);
            if (s != "OK")
            {
                return "User not authorized to sign note: " + s;
            }
            if (!hasAuthorSignedNote(noteIEN, DUZ))
            {
                return "Author has not signed note";
            }
            VistaUserDao userDao = new VistaUserDao(cxn);
            if (!userDao.isValidEsig(esig))
            {
                return "Invalid signature";
            }
            return signNote(noteIEN,esig);
        }

        public string closeNote(string noteIEN, string consultIEN)
        {
            VistaUtils.CheckRpcParams(noteIEN);
            unlockNote(noteIEN);
            string text = getNoteText(noteIEN);
            if (!String.IsNullOrEmpty(consultIEN))
            {
                VistaUtils.CheckRpcParams(consultIEN);
                VistaConsultDao consultDao = new VistaConsultDao(cxn);
                string orderIEN = consultDao.getOrderNumberForConsult(consultIEN);
                if (orderIEN == "")
                {
                    // Do nothing - CPRS assumes it's an Interfacility Consult
                }
                else
                {
                    VistaOrdersDao ordersDao = new VistaOrdersDao(cxn);
                    if (!ordersDao.unlockOrder(orderIEN))
                    {
                        throw new RecordLockingException("Unable to unlock consult " + consultIEN + " for order " + orderIEN);
                    }
                }
            }
            string permission = isUserAuthorized(noteIEN, "VIEW");
            if (permission != "OK")     // Note is signed but unviewable (weird)
            {
                return "OK";
            }
            return text;
        }

        #region HelperMethods
        public string getEnterpriseTitle(string id)
        {
            var result = getEnterpriseTitles();
            return result[id];
        }

        public string getEnterpriseId(string title)
        {
            String[] enterpriseTitles;
            var result = getEnterpriseTitles();
            enterpriseTitles = new String[result.Count];

            Dictionary<string, string> reverseDict = new Dictionary<string, string>();
            foreach (DictionaryEntry kv in result)
            {
                reverseDict.Add(kv.Value.ToString(), kv.Key.ToString());
            };
            return reverseDict[title];
        }
        #endregion //HelperMethods

        #endregion

    }
}

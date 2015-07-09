using System;
using System.Web;
using System.Collections.Specialized;
using System.Collections.Generic;
using System.Collections;
using gov.va.medora.mdo;
using gov.va.medora.utils;
using gov.va.medora.mdo.api;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    public class NoteLib
    {
        MySession _mySession;
        NoteApi _api;

        public NoteLib(MySession mySession)
        {
            this._mySession = mySession;
            this._api = new NoteApi();
        }

        public TaggedTextArray getNoteTitles(string target, string direction)
        {
            return getNoteTitles(null, target, direction);
        }

        public TaggedTextArray getNoteTitles(string sitecode, string target, string direction)
        {
            TaggedTextArray result = new TaggedTextArray();
            string msg = MdwsUtils.isAuthorizedConnection(_mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }

            if (sitecode == null)
            {
                sitecode = _mySession.ConnectionSet.BaseSiteId;
            }

            if (direction == "")
            {
                direction = "1";
            }

            try
            {
                AbstractConnection cxn = _mySession.ConnectionSet.getConnection(sitecode);
                Dictionary<string, ArrayList> x = _api.getNoteTitles(cxn, target, direction);
                IndexedHashtable t = new IndexedHashtable();
                t.Add(sitecode, x);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedText isSurgeryNote(string noteDefinitionIEN)
        {
            return isSurgeryNote(null, noteDefinitionIEN);
        }

        public TaggedText isSurgeryNote(string sitecode, string noteDefinitionIEN)
        {
            TaggedText result = new TaggedText();
            string msg = MdwsUtils.isAuthorizedConnection(_mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (noteDefinitionIEN == "")
            {
                result.fault = new FaultTO("Missing noteDefinitionIEN");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = _mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = _mySession.ConnectionSet.getConnection(sitecode);
                bool f = _api.isSurgeryNote(cxn, noteDefinitionIEN);
                result.tag = (f ? "Y" : "N");
                result.text = (f ? "Cannot create new surgery note at this time" : "");
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedText isOneVisitNote(string noteDefinitionIEN, string noteTitle, string visitStr)
        {
            return isOneVisitNote(null, noteDefinitionIEN, noteTitle, visitStr);
        }

        public TaggedText isOneVisitNote(string sitecode, string noteDefinitionIEN, string noteTitle, string visitStr)
        {
            TaggedText result = new TaggedText();
            string msg = MdwsUtils.isAuthorizedConnection(_mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (String.IsNullOrEmpty(noteDefinitionIEN))
            {
                result.fault = new FaultTO("Missing noteDefinitionIEN");
            }
            // TODO - this arg is superfluous - remove from future versions or rename to dfn (patient IEN) to remove need to select patient
            //else if (String.IsNullOrEmpty(noteTitle))
            //{
            //    result.fault = new FaultTO("Missing noteTitle");
            //}
            else if (String.IsNullOrEmpty(visitStr))
            {
                result.fault = new FaultTO("Missing visitStr");
            }
            else if (String.IsNullOrEmpty(_mySession.ConnectionSet.BaseConnection.Pid))
            {
                result.fault = new FaultTO("No patient selected", "Need to select patient");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = _mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = _mySession.ConnectionSet.getConnection(sitecode);
                bool f = _api.isOneVisitNote(cxn, noteDefinitionIEN, _mySession.ConnectionSet.BaseConnection.Pid, visitStr);
                if (f)
                {
                    result.tag = "Y";
                    result.text = "There is already a " + noteTitle + " note for this visit.\r\n" +
                        "Only ONE record of this type per Visit is allowed...\r\n\r\n" +
                        "You can addend the existing record.";
                }
                else
                {
                    result.tag = "N";
                    result.text = "";
                }
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TextTO isConsultNote(string noteDefinitionIEN)
        {
            return isConsultNote(null, noteDefinitionIEN);
        }

        public TextTO isConsultNote(string sitecode, string noteDefinitionIEN)
        {
            TextTO result = new TextTO();
            string msg = MdwsUtils.isAuthorizedConnection(_mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (noteDefinitionIEN == "")
            {
                result.fault = new FaultTO("Missing noteDefinitionIEN");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = _mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = _mySession.ConnectionSet.getConnection(sitecode);
                bool f = _api.isConsultNote(cxn, noteDefinitionIEN);
                result.text = (f ? "Y" : "N");
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TextTO isPrfNote(string noteDefinitionIEN)
        {
            return isPrfNote(null, noteDefinitionIEN);
        }

        public TextTO isPrfNote(string sitecode, string noteDefinitionIEN)
        {
            TextTO result = new TextTO();
            string msg = MdwsUtils.isAuthorizedConnection(_mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (noteDefinitionIEN == "")
            {
                result.fault = new FaultTO("Missing noteDefinitionIEN");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = _mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = _mySession.ConnectionSet.getConnection(sitecode);
                bool f = _api.isPrfNote(cxn, noteDefinitionIEN);
                result.text = (f ? "Y" : "N");
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TextTO isCosignerRequired(string noteDefinitionIEN, string authorDUZ)
        {
            return isCosignerRequired(null, noteDefinitionIEN, authorDUZ);
        }

        public TextTO isCosignerRequired(string sitecode, string noteDefinitionIEN, string authorDUZ)
        {
            TextTO result = new TextTO();
            string msg = MdwsUtils.isAuthorizedConnection(_mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (noteDefinitionIEN == "")
            {
                result.fault = new FaultTO("Missing noteDefinitionIEN");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = _mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = _mySession.ConnectionSet.getConnection(sitecode);
                bool f = false;
                if (authorDUZ == "")
                {
                    f = _api.isCosignerRequired(cxn, _mySession.User.Uid, noteDefinitionIEN);
                }
                else
                {
                    f = _api.isCosignerRequired(cxn, _mySession.User.Uid, noteDefinitionIEN, authorDUZ);
                }
                result.text = (f ? "Y" : "N");
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TextTO getNoteText(string noteIEN)
        {
            return getNoteText(null, noteIEN);
        }

        public TextTO getNoteText(string sitecode, string noteIEN)
        {
            TextTO result = new TextTO();
            string msg = MdwsUtils.isAuthorizedConnection(_mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (noteIEN == "")
            {
                result.fault = new FaultTO("Missing noteIEN");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = _mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = _mySession.ConnectionSet.getConnection(sitecode);
                result.text = _api.getNoteText(cxn, noteIEN);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public PatientRecordFlagArray getPrfNoteActions(string noteDefinitionIEN)
        {
            return getPrfNoteActions(null, noteDefinitionIEN);
        }

        public PatientRecordFlagArray getPrfNoteActions(string sitecode, string noteDefinitionIEN)
        {
            PatientRecordFlagArray result = new PatientRecordFlagArray();
            string msg = MdwsUtils.isAuthorizedConnection(_mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (noteDefinitionIEN == "")
            {
                result.fault = new FaultTO("Missing noteDefinitionIEN");
            }
            if (result.fault != null)
            {
                return result;
            }
            if (sitecode == null)
            {
                sitecode = _mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = _mySession.ConnectionSet.getConnection(sitecode);
                PatientRecordFlag[] flags = _api.getPrfNoteActions(cxn, noteDefinitionIEN);
                result = new PatientRecordFlagArray(flags);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public NoteResultTO writeNote(
            string titleIEN,
            string encounterString,
            string text,
            string authorDUZ,
            string cosignerDUZ,
            string consultIEN,
            string prfIEN)
        {
            return writeNote(null, titleIEN, encounterString, text, authorDUZ, cosignerDUZ, consultIEN, prfIEN);
        }

        public NoteResultTO writeNote(
            string sitecode,
            string titleIEN,
            string encounterString,
            string text,
            string authorDUZ,
            string cosignerDUZ,
            string consultIEN,
            string prfIEN)
        {
            NoteResultTO result = new NoteResultTO();
            string msg = MdwsUtils.isAuthorizedConnection(_mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (titleIEN == "")
            {
                result.fault = new FaultTO("Missing titleIEN");
            }
            else if (encounterString == "")
            {
                result.fault = new FaultTO("Missing encounterString");
            }
            else if (text == "")
            {
                result.fault = new FaultTO("No text!");
            }

            Encounter encounter = null;
            try
            {
                encounter = getFromEncounterString(encounterString);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            if (result.fault != null)
            {
                return result;
            }

            // If no author DUZ we assume author is user
            if (authorDUZ == "")
            {
                authorDUZ = _mySession.User.Uid;
            }

            if (sitecode == null)
            {
                sitecode = _mySession.ConnectionSet.BaseSiteId;
            }

            // Externalizing note writing business rules...
            //Note note = new Note();
            //note.DocumentDefinitionId = titleIEN;
            //note.Author = new Author(authorDUZ, "", "");
            //note.Timestamp = DateTime.Now.ToString("yyyyMMdd");
            //note.ConsultId = consultIEN;
            //note.IsAddendum = false;
            //note.PrfId = prfIEN;

            //if (cosignerDUZ != "")
            //{
            //    note.Cosigner = new Author(cosignerDUZ, "", "");
            //}
            //note.Text = text;

            try
            {
                AbstractConnection cxn = _mySession.ConnectionSet.getConnection(sitecode);
                NoteResult noteResult = _api.writeNote(
                    cxn, titleIEN, encounter, text, authorDUZ, cosignerDUZ, consultIEN, prfIEN
                    );
                return new NoteResultTO(noteResult);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
                result.fault.stackTrace = e.StackTrace;
            }
            return result;
        }

        public TextTO signNote(string noteId, string userId, string esig)
        {
            return signNote(null, noteId, userId, esig);
        }

        public TextTO signNote(string sitecode, string noteId, string userId, string esig)
        {
            TextTO result = new TextTO();
            string msg = MdwsUtils.isAuthorizedConnection(_mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (noteId == "")
            {
                result.fault = new FaultTO("Missing noteId");
            }
            else if (esig == "")
            {
                result.fault = new FaultTO("Missing esig");
            }
            if (userId == "")
            {
                if (_mySession.User.Uid == "")
                {
                    result.fault = new FaultTO("Missing userId");
                }
                else
                {
                    userId = _mySession.User.Uid;
                }
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = _mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = _mySession.ConnectionSet.getConnection(sitecode);
                NoteApi api = new NoteApi();
                string s = api.signNote(cxn, noteId, userId, esig);
                if (s != "OK")
                {
                    result.fault = new FaultTO(s);
                }
                else
                {
                    result = new TextTO(s);
                }
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TextTO closeNote(string noteId, string consultId)
        {
            return closeNote(null, noteId, consultId);
        }

        public TextTO closeNote(string sitecode, string noteId, string consultId)
        {
            TextTO result = new TextTO();
            string msg = MdwsUtils.isAuthorizedConnection(_mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (noteId == "")
            {
                result.fault = new FaultTO("Missing noteId");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = _mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = _mySession.ConnectionSet.getConnection(sitecode);
                NoteApi api = new NoteApi();
                string s = api.closeNote(cxn, noteId, consultId);
                result = new TextTO(s);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        internal Encounter getFromEncounterString(string encounterString)
        {
            string[] flds = StringUtils.split(encounterString, StringUtils.SEMICOLON);
            if (flds.Length != 3)
            {
                throw new Exception("Invalid encounter string: does not contain 3 parts");
            }
            if (!StringUtils.isNumeric(flds[0]))
            {
                throw new Exception("Invalid encounter string: non-numeric location IEN");
            }
            //TBD: how to test for valid VistA timestamp (fld[1])
            if (flds[2] != "A" && flds[2] != "H" && flds[2] != "E")
            {
                throw new Exception("Invalid encounter string: type must be A, H or E");
            }
            Encounter result = new Encounter();
            result.LocationId = flds[0];
            result.Timestamp = flds[1];
            result.Type = flds[2];
            return result;
        }

        public TaggedNoteArrays getSignedNotes(string fromDate, string toDate, int nNotes)
        {
            TaggedNoteArrays result = new TaggedNoteArrays();

            string msg = MdwsUtils.isAuthorizedConnection(_mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                NoteApi api = new NoteApi();
                IndexedHashtable t = api.getSignedNotes(_mySession.ConnectionSet, fromDate, toDate, nNotes);
                result = new TaggedNoteArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedNoteArrays getUnsignedNotes(string fromDate, string toDate, int nNotes)
        {
            TaggedNoteArrays result = new TaggedNoteArrays();

            string msg = MdwsUtils.isAuthorizedConnection(_mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                NoteApi api = new NoteApi();
                IndexedHashtable t = api.getUnsignedNotes(_mySession.ConnectionSet, fromDate, toDate, nNotes);
                result = new TaggedNoteArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedNoteArrays getUncosignedNotes(string fromDate, string toDate, int nNotes)
        {
            TaggedNoteArrays result = new TaggedNoteArrays();

            string msg = MdwsUtils.isAuthorizedConnection(_mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                NoteApi api = new NoteApi();
                IndexedHashtable t = api.getUncosignedNotes(_mySession.ConnectionSet, fromDate, toDate, nNotes);
                result = new TaggedNoteArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TextTO getNote(string siteId, string noteId)
        {
            TextTO result = new TextTO();

            string msg = MdwsUtils.isAuthorizedConnection(_mySession, siteId);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (siteId == "")
            {
                result.fault = new FaultTO("Missing siteId");
            }
            else if (noteId == "")
            {
                result.fault = new FaultTO("Missing noteId");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                NoteApi api = new NoteApi();
                string s = api.getNoteText(_mySession.ConnectionSet.getConnection(siteId), noteId);
                result = new TextTO(s);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedNoteArrays getNotesWithText(string fromDate, string toDate, int nNotes)
        {
            TaggedNoteArrays result = new TaggedNoteArrays();

            if (!_mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                NoteApi api = new NoteApi();
                IndexedHashtable t = api.getNotes(_mySession.ConnectionSet, fromDate, toDate, nNotes);
                result = new TaggedNoteArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedNoteArrays getDischargeSummaries(String fromDate, String toDate, int nNotes)
        {
            TaggedNoteArrays result = new TaggedNoteArrays();

            string msg = MdwsUtils.isAuthorizedConnection(_mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                NoteApi api = new NoteApi();
                IndexedHashtable t = api.getDischargeSummaries(_mySession.ConnectionSet, fromDate, toDate, nNotes);
                result = new TaggedNoteArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray getAdvanceDirectives(string fromDate, string toDate, int nrpts)
        {
            TaggedTextArray result = new TaggedTextArray();

            string msg = MdwsUtils.isAuthorizedConnection(_mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                NoteApi api = new NoteApi();
                IndexedHashtable t = api.getAdvanceDirectives(_mySession.ConnectionSet, fromDate, toDate, nrpts);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedTextArray getClinicalWarnings(string fromDate, string toDate, int nrpts)
        {
            TaggedTextArray result = new TaggedTextArray();

            string msg = MdwsUtils.isAuthorizedConnection(_mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                NoteApi api = new NoteApi();
                IndexedHashtable t = api.getClinicalWarnings(_mySession.ConnectionSet, fromDate, toDate, nrpts);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedTextArray getCrisisNotes(string fromDate, string toDate, int nrpts)
        {
            TaggedTextArray result = new TaggedTextArray();

            string msg = MdwsUtils.isAuthorizedConnection(_mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                NoteApi api = new NoteApi();
                IndexedHashtable t = api.getCrisisNotes(_mySession.ConnectionSet, fromDate, toDate, nrpts);
                return new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
                return result;
            }
        }

        public TaggedNoteArrays getNotesForBhie(string pwd, string mpiPid, string fromDate, string toDate, string nNotes)
        {
            TaggedNoteArrays result = new TaggedNoteArrays();
            if (pwd != "iBnOfs55iEZ,d")
            {
                result.fault = new FaultTO("Invalid application password");
            }
            else if (String.IsNullOrEmpty(mpiPid))
            {
                result.fault = new FaultTO("Missing mpiPid");
            }
            else if (String.IsNullOrEmpty(fromDate))
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (String.IsNullOrEmpty(toDate))
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (nNotes == "")
            {
                nNotes = "50";
            }

            try
            {
                int maxNotes = Convert.ToInt16(nNotes);
                PatientLib patLib = new PatientLib(_mySession);
                TaggedTextArray sites = patLib.getPatientSitesByMpiPid(mpiPid);
                if (sites == null)
                {
                    return null;
                }
                if (sites.fault != null)
                {
                    result.fault = sites.fault;
                    return result;
                }

                string sitelist = "";
                for (int i = 0; i < sites.count; i++)
                {
                    if ((string)sites.results[i].tag == "200")
                    {
                        continue;
                    }
                    if (sites.results[i].fault != null)
                    {
                    }
                    else
                    {
                        sitelist += (string)sites.results[i].tag + ',';
                    }
                }
                sitelist = sitelist.Substring(0, sitelist.Length - 1);

                AccountLib acctLib = new AccountLib(_mySession);
                sites = acctLib.visitSites("BHIE", sitelist, MdwsConstants.CPRS_CONTEXT);
                if (sites.fault != null)
                {
                    result.fault = sites.fault;
                    return result;
                }

                PatientApi patApi = new PatientApi();
                IndexedHashtable t = patApi.setLocalPids(_mySession.ConnectionSet, mpiPid);
                NoteApi api = new NoteApi();
                IndexedHashtable tNotes = api.getNotes(_mySession.ConnectionSet, fromDate, toDate, maxNotes);
                IndexedHashtable tSumms = api.getDischargeSummaries(_mySession.ConnectionSet, fromDate, toDate, 50);
                result = new TaggedNoteArrays(mergeNotesAndDischargeSummaries(tNotes, tSumms));
            }
            catch (Exception ex)
            {
                result.fault = new FaultTO(ex);
            }
            finally
            {
                if (_mySession.ConnectionSet != null)
                {
                    _mySession.ConnectionSet.disconnectAll();
                }
            }
            return result;
        }

        private IndexedHashtable mergeNotesAndDischargeSummaries(IndexedHashtable tNotes, IndexedHashtable tSummaries)
        {
            if (tNotes == null)
            {
                return tSummaries;
            }
            if (tSummaries == null)
            {
                return tNotes;
            }
            IndexedHashtable result = new IndexedHashtable(tNotes.Count + tSummaries.Count);
            for (int i = 0; i < tNotes.Count; i++)
            {
                Note[] notes = (Note[])tNotes.GetValue(i);
                int notesLength = (notes == null ? 0 : notes.Length);
                string key = (string)tNotes.GetKey(i);
                Note[] summaries = (Note[])tSummaries.GetValue(key);
                int summariesLength = (summaries == null ? 0 : summaries.Length);
                ArrayList lst = new ArrayList(notesLength + summariesLength);
                for (int j = 0; j < notesLength; j++)
                {
                    lst.Add(notes[j]);
                }
                for (int j = 0; j < summariesLength; j++)
                {
                    lst.Add(summaries[j]);
                }
                result.Add(key, (Note[])lst.ToArray(typeof(Note)));
            }
            return result;
        }
    }
}

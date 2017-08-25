using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Collections;
using System.Text;
using System.IO;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;
using System.Configuration;
using System.Xml;
using gov.va.medora.mdo.src.utils;
using gov.va.medora.mdo.domain.ccr;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaClinicalDao : IClinicalDao
    {
         AbstractConnection cxn = null;

         public VistaClinicalDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        #region Health Summaries

        public AdHocHealthSummary[] getAdHocHealthSummaries()
        {
            MdoQuery request = buildGetAdHocHealthSummariesRequest();
            string response = (string)cxn.query(request);
            return toAdHocHealthSummaries(response);
        }

        internal MdoQuery buildGetAdHocHealthSummariesRequest()
        {
            VistaQuery vq = new VistaQuery("ORWRP2 HS COMPONENTS");
            return vq;
        }

        internal AdHocHealthSummary[] toAdHocHealthSummaries(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            AdHocHealthSummary[] result = new AdHocHealthSummary[lines.Length];
            for (int i = 0; i < lines.Length; i++)
            {
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                result[i] = new AdHocHealthSummary();
                result[i].Id = flds[0];
                result[i].Title = flds[4];
            }
            return result;
        }

        public string getAdHocHealthSummaryByDisplayName(string displayName)
        {
            return getAdHocHealthSummaryByDisplayName(cxn.Pid, displayName);
        }

        public string getAdHocHealthSummaryByDisplayName(string dfn, string displayName)
        {
            AdHocHealthSummary hs = getAdHocHealthSummaryDataByDisplayName(displayName);
            if (hs == null)
            {
                return null;
            }
            return getAdHocHealthSummary(dfn, hs.Id, hs.Title);
        }

        public AdHocHealthSummary getAdHocHealthSummaryDataByDisplayName(string displayName)
        {
            MdoQuery request = buildGetAdHocHealthSummariesRequest();
            string response = (string)cxn.query(request, new MenuOption(VistaConstants.CPRS_CONTEXT));
            return toAdHocHealthSummary(displayName, response);
        }

        internal AdHocHealthSummary toAdHocHealthSummary(string displayName, string response)
        {
            if (String.IsNullOrEmpty(response))
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            for (int i = 0; i < lines.Length; i++)
            {
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                if (String.Equals(flds[1], displayName, StringComparison.CurrentCultureIgnoreCase))
                {
                    AdHocHealthSummary result = new AdHocHealthSummary();
                    result.Id = flds[0];
                    result.Title = flds[4];
                    return result;
                }
            }
            return null;
        }

        public string getAdHocHealthSummary(string IEN, string title)
        {
            return getAdHocHealthSummary(cxn.Pid, IEN, title);
        }

        public string getAdHocHealthSummary(string DFN, string IEN, string title)
        {
            MdoQuery request = buildGetAdHocHealthSummaryRequest(DFN, IEN, title);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetAdHocHealthSummaryRequest(string DFN, string IEN, string title)
        {
            VistaQuery vq = new VistaQuery("ORWRP2 HS REPORT TEXT");
            DictionaryHashList lst = new DictionaryHashList();
            lst.Add("1", IEN + "^^^" + title + "^^^");
            vq.addParameter(vq.LIST, lst);
            vq.addParameter(vq.LITERAL, DFN);
            return vq;
        }

        public MdoDocument[] getHealthSummaryList()
        {
            MdoQuery request = buildGetHealthSummaryListRequest();
            string response = (string)cxn.query(request);
            return toMdoDocuments(response);
        }

        internal MdoQuery buildGetHealthSummaryListRequest()
        {
            VistaQuery vq = new VistaQuery("ORWRP REPORT LISTS");
            return vq;
        }

        /// <summary>Parses ^-delim into MdoDocuments for Health Summary Types
        /// </summary>
        /// <remarks>
        /// Throws Exception if no summary types are returned.
        /// </remarks>
        /// <param name="response">response string from VistA</param>
        /// <returns></returns>
        internal MdoDocument[] toMdoDocuments(string response)
        {
            if (response == "")
            {
                throw new Exception("No summary types returned");
            }
            string[] rex = StringUtils.split(response, StringUtils.CRLF);
            ArrayList lst = new ArrayList();
            int i = 0;
            while (rex[i] != "[HEALTH SUMMARY TYPES]")
            {
                i++;
            }
            while (rex[++i] != "$$END")
            {
                string[] parts = StringUtils.split(rex[i], StringUtils.CARET);
                MdoDocument hs = new MdoDocument(parts[0].Substring(1), parts[1]);
                lst.Add(hs);
            }
            return (MdoDocument[])lst.ToArray(typeof(MdoDocument));
        }

        public string getHealthSummaryTitle(string IEN)
        {
            string arg = "$P(^GMT(142," + IEN + ",0),\"^\",1)";
            string response = VistaUtils.getVariableValue(cxn,arg);
            return toHealthSummaryTitle(response);
        }

        internal string toHealthSummaryTitle(string response)
        {
            if (response == "")
            {
                return "";
            }
            int index = response.IndexOf('^');
            if (index > -1)
            {
                return response.Substring(0, index);
            }
            else
            {
                return response;
            }
        }

	    public string getHealthSummaryText(string mpiPid, MdoDocument hs, string sourceSiteId)
        {
            MdoQuery request = buildGetHealthSummaryTextRequest(mpiPid, hs, sourceSiteId);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetHealthSummaryTextRequest(string mpiPid, MdoDocument hs, string sourceSiteId)
        {
		    VistaQuery vq = new VistaQuery("ORWRP REPORT TEXT");
		    vq.addParameter(vq.LITERAL,"0;" + mpiPid);
		    vq.addParameter(vq.LITERAL,"1;1~");
		    if (sourceSiteId == cxn.DataSource.SiteId.Id) 
            {
                vq.addParameter(vq.LITERAL,hs.Id + ";" + getHealthSummaryTitle(hs.Id));
		    }
		    else 
            {
			    vq.addParameter(vq.LITERAL,hs.Id + ";" + hs.Title.ToUpper());
		    }
		    vq.addParameter(vq.LITERAL,"");
		    vq.addParameter(vq.LITERAL,"");
		    vq.addParameter(vq.LITERAL,"0");
		    vq.addParameter(vq.LITERAL,"0");
            return vq;
	    }

        /// <summary>Gets a local health summary
        /// </summary>
        /// <param name="dfn">The patient DFN</param>
        /// <param name="hs">MdoDocument with the health summary name and/or health summary ien.</param>
        /// <returns>requested health summary or null if not found.</returns>
        public HealthSummary getHealthSummary(string dfn, MdoDocument hs)
        {
            MdoQuery request = buildGetHealthSummaryRequest(dfn, hs);
            string response = (string)cxn.query(request);
            return toHealthSummary(hs, response);
        }

        public HealthSummary getHealthSummary(MdoDocument hs)
        {
            return getHealthSummary(cxn.Pid,hs);
        }

        /// <summary> Gets health summary IEN based on displayName
        /// </summary>
        /// <remarks>
        /// VAN 2012-05-14: this could be useful for other report types but is currently limited by the getHealthSummaryList() call. 
        /// Perhaps consider extending at a later date.
        /// 
        /// Also, there has to be a better way to do this (like a different xref based on health summary title), so this should be temporary.
        /// </remarks>
        /// <param name="displayName"></param>
        /// <returns></returns>
        internal string getHealthSummaryIdByDisplayName(string displayName)
        {
            if (string.IsNullOrEmpty(displayName))
            {
                return null;
            }
            try
            {
                //throws an Exception if no health summary list was returned
                MdoDocument[] summaries = getHealthSummaryList();
                for (int i = 0; i < summaries.Length; i++)
                {
                    if (summaries[i].Title.ToUpper() == displayName.ToUpper())
                    {
                        return summaries[i].Id;
                    }
                }

            }
            catch (Exception e)
            {
                // TBD van 2012-05-14 need to decide what I want to do with this.
                // throw e;
            }
            return null;
        }

        /// <summary>
        /// literal	7197376
//literal	11:ORDER SUMMARY FOR A DATE RANGE~;;0;101
//literal	
//literal	180
//literal	
//literal	0
//literal	0

        public String getOrderSummaryForLast6Months()
        {
            MdoQuery request = buildGetOrderSummaryForDateRangeRequest();
            string response = (string)cxn.query(request);
            return response;
        }
        /// </summary>
        /// <returns></returns>
        internal MdoQuery buildGetOrderSummaryForDateRangeRequest()
        {
            VistaQuery vq = new VistaQuery("ORWRP REPORT TEXT");
            vq.addParameter(vq.LITERAL, "22339");
            vq.addParameter(vq.LITERAL, "11:ORDER SUMMARY FOR A DATE RANGE~;;0;101");
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, "180");
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, "0");
            vq.addParameter(vq.LITERAL, "0");
            return vq;
        }



        /// <summary>
        /// </summary>
        /// <remarks>
        /// Health Summaries are pulled by their IEN, so if we don't have one for a title we need to get it based on the display name.
        /// </remarks>
        /// <param name="dfn"></param>
        /// <param name="hs">MdoDocument containing the local display name and the local ien of the summary</param>
        /// <returns>MdoQuery or MdoException if can't get a valid Health Summary IEN.</returns>
        internal MdoQuery buildGetHealthSummaryRequest(string dfn, MdoDocument hs)
        {
            // TBD VAN 2012-05-15 consider refactoring to encapsulate
            if (string.IsNullOrEmpty(hs.Id))
            {
                hs.Id = getHealthSummaryIdByDisplayName(hs.Title);
                if (string.IsNullOrEmpty(hs.Id))
                {
                    throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Missing Health Summary identification. Please provide a valid health summary IEN or Name.");
                }
            }
            else if (string.IsNullOrEmpty(hs.Title))
            {
                hs.Title = getHealthSummaryTitle(hs.Id);
            }
            VistaQuery vq = new VistaQuery("ORWRP REPORT TEXT");
            vq.addParameter(vq.LITERAL, dfn);
            vq.addParameter(vq.LITERAL, "1");
            vq.addParameter(vq.LITERAL, hs.Id);
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, "0");
            vq.addParameter(vq.LITERAL, "0");
            return vq;
        }

        internal HealthSummary toHealthSummary(MdoDocument md, string response)
        {
            HealthSummary hs = new HealthSummary();
            hs.Id = md.Id;
            hs.Title = md.Title;
            hs.Text = response;
            return hs;
        }


        #endregion

        #region Allergies

        public Allergy[] getAllergies()
        {
            return getAllergies(cxn.Pid);
        }

        public Allergy[] getAllergies(string dfn)
        {
            MdoQuery request = buildGetAllergiesRequest(dfn);
            string response = (string)cxn.query(request);

            MdoQuery coverRequest = buildGetCoverSheetAllergiesRequest(dfn);
            string coverResponse = (string)cxn.query(coverRequest);

            Allergy[] rdvAllergies = toAllergies(response);
            Allergy[] coverAllergies = toAllergiesFromCover(coverResponse);

            return supplementAllergies(coverAllergies, rdvAllergies);
        }

        //Added a toDate call to the interface, to allow automated tests to run 
        //succesfully.
        public Allergy[] getAllergies(string dfn,string toDate)
        {
            MdoQuery request = buildGetAllergiesRequest(dfn,toDate);
            string response = (string)cxn.query(request);

            MdoQuery coverRequest = buildGetCoverSheetAllergiesRequest(dfn);
            string coverResponse = (string)cxn.query(coverRequest);

            Allergy[] rdvAllergies = toAllergies(response);
            Allergy[] coverAllergies = toAllergiesFromCover(coverResponse);

            return supplementAllergies(coverAllergies, rdvAllergies);
        }

        internal MdoQuery buildGetAllergiesRequest(string dfn)
        {
            return VistaUtils.buildReportTextRequest(dfn, "", "", 0, "OR_BADR:ALLERGIES~ADR;ORDV01;73;");
        }
        
        //Added a toDate call to the interface, to allow automated tests to run 
        //succesfully.
        internal MdoQuery buildGetAllergiesRequest(string dfn, String toDate)
        {
            return VistaUtils.buildReportTextRequest(dfn, "", toDate, 0, "OR_BADR:ALLERGIES~ADR;ORDV01;73;");
        }

        //ORQQAL LIST
        //Params ------------------------------------------------------------------
        //literal	100848
        //Results -----------------------------------------------------------------
        //973^NONSTEROIDAL ANTI-INFLAMMATORY^^ABDOMINAL PAIN
        //985^PENICILLIN^
        internal MdoQuery buildGetCoverSheetAllergiesRequest(string dfn)
        {
            VistaQuery vq = new VistaQuery("ORQQAL LIST");
            vq.addParameter(vq.LITERAL, dfn);
            return vq;
        }

        internal Allergy[] supplementAllergies(Allergy[] coverSheetAllergies, Allergy[] rdvAllergies)
        {
            if (coverSheetAllergies == null || coverSheetAllergies.Length == 0)
            {
                return rdvAllergies;
            }
            if (rdvAllergies == null || rdvAllergies.Length == 0)
            {
                return coverSheetAllergies;
            }

            foreach (Allergy allergy in rdvAllergies)
            {
                for (int i = 0; i < coverSheetAllergies.Length; i++)
                {
                    if (String.Equals(coverSheetAllergies[i].AllergenId, allergy.AllergenId))
                    {
                        //allergy.AllergenId = coverSheetAllergies[i].AllergenId;
                        allergy.Reactions = coverSheetAllergies[i].Reactions;
                        break;
                    }
                }
            }

            return rdvAllergies;
        }

        internal Allergy[] toAllergiesFromCover(string response)
        {
            if (String.IsNullOrEmpty(response))
            {
                return new Allergy[0];
            }

            string[] lines = response.Split(new string[] { "\r\n" }, StringSplitOptions.RemoveEmptyEntries);

            if (lines == null || lines.Length == 0)
            {
                return new Allergy[0];
            }

            IList<Allergy> allergies = new List<Allergy>();

            foreach (string line in lines)
            {
                string[] flds = line.Split(new char[] { '^' });

                if (flds == null || flds.Length == 0)
                {
                    continue;
                }

                Allergy newAllergy = new Allergy();
                newAllergy.AllergenId = flds[0];
                if (flds.Length > 1)
                {
                    newAllergy.AllergenName = flds[1];
                }
                if (flds.Length > 2)
                {
                    // what is in field 2???
                }
                if (flds.Length > 3)
                {
                    newAllergy.Reactions = new List<Symptom>() { new Symptom() { Name = flds[3] } };
                }

                allergies.Add(newAllergy);
            }

            Allergy[] result = new Allergy[allergies.Count];
            allergies.CopyTo(result, 0);
            return result;
        }

        //        ORWRP REPORT TEXT
 
        //Params ------------------------------------------------------------------
        //literal	100848
        //literal	OR_BADR:ALLERGIES~ADR;ORDV01;73;10
        //literal	
        //literal	
        //literal	
        //literal	3120319
        //literal	3120326
 
        //Results -----------------------------------------------------------------
        //1^CAMP MASTER;500
        //2^NONSTEROIDAL ANTI-INFLAMMATORY
        //3^DRUG
        //4^04/03/2011 08:25
        //5^HISTORICAL
        //7^973
        //1^CAMP MASTER;500
        //2^PENICILLIN
        //3^DRUG
        //4^11/28/2011 17:54
        //5^HISTORICAL
        //7^985

        internal Allergy[] toAllergies(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            ArrayList lst = new ArrayList();
            Allergy rec = null;
            string comment = "";
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i] == "")
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                int fldnum = Convert.ToInt16(flds[0]);
                switch (fldnum)
                {
                    case 1:
                        if (rec != null)
                        {
                            rec.Comment = comment;
                            lst.Add(rec);
                        }
                        rec = new Allergy();
                        comment = "";
                        string[] parts = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                        if (parts.Length == 2)
                        {
                            rec.Facility = new SiteId(parts[1], parts[0]);
                        }
                        else if (flds[1] != "")
                        {
                            rec.Facility = new SiteId(cxn.DataSource.SiteId.Id, flds[1]);
                        }
                        else
                        {
                            rec.Facility = cxn.DataSource.SiteId;
                        }
                        break;
                    case 2:
                        if (flds.Length == 2)
                        {
                            rec.AllergenName = flds[1];
                        }
                        break;
                    case 3:
                        if (flds.Length == 2)
                        {
                            rec.AllergenType = flds[1];
                        }
                        break;
                    case 4:
                        if (flds.Length == 2)
                        {
                            rec.Timestamp = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 5:
                        if (flds.Length == 2)
                        {
                            rec.Type = new ObservationType("","Allergies and Adverse Reactions", flds[1]);
                        }
                        break;
                    case 6:
                        if (flds.Length == 2)
                        {
                            comment += flds[1] + '\n';
                        }
                        break;
                    case 7:
                        if (flds.Length == 2)
                        {
                            rec.AllergenId = flds[1];
                        }
                        break;
                }
            }
            if (rec != null)
            {
                lst.Add(rec);
            }
            return (Allergy[])lst.ToArray(typeof(Allergy));
        }

        public string getAllergiesAsXML()
        {
            throw new NotImplementedException();
        }


        public Allergy[] getAllergiesBySite(string siteCode)
        {
            return getAllergies();
        }

        #endregion

        #region Problem List

        public Problem[] getProblemList(string type)
        {
            return getProblemList(cxn.Pid, type);
        }

        public Problem[] getProblemList(string dfn, string type)
        {
            MdoQuery request = buildGetProblemListRequest(dfn, type);
            string response = (string)cxn.query(request);
            return toProblemList(response);
        }

        internal MdoQuery buildGetProblemListRequest(string dfn, string type)
        {
            if (!VistaUtils.isWellFormedIen(dfn))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid DFN: " + dfn);
            }
            if (String.IsNullOrEmpty(type))
            {
                type = "ALL"; // get all problems if type was not specified
            }
            type = type.ToUpper();
            string arg = "";
            if (type == "ACTIVE" || type == "A")
            {
                arg = "OR_PLA:ACTIVE PROBLEMS~PLAILA;ORDV04;59;";
            }
            else if (type == "INACTIVE" || type == "I")
            {
                arg = "OR_PLI:INACTIVE PROBLEMS~PLAILI;ORDV04;60;";
            }
            else if (type == "ALL")
            {
                arg = "OR_DODPLL:ALL PROBLEM LIST~PLAILALL;ORDV04;61;";
            }
            else
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid type request: " + type + ".  Must be 'ACTIVE', 'INACTIVE', or 'ALL'.");
            }
            VistaQuery vq = new VistaQuery("ORWRP REPORT TEXT");
            vq.addParameter(vq.LITERAL, dfn);
            vq.addParameter(vq.LITERAL, arg + '0');
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, "");

            // Time frame has been removed since VistA doesn't use them...
            //vq.addParameter(vq.LITERAL, VistaTimestamp.fromUtcFromDate(fromDate));
            //vq.addParameter(vq.LITERAL, VistaTimestamp.fromUtcToDate(toDate));

            return vq;
        }

        internal Problem[] toProblemList(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            ArrayList lst = new ArrayList();
            Problem rec = null;
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i] == "")
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                int fldnum = Convert.ToInt32(flds[0]);
                switch (fldnum)
                {
                    case 1:
                        if (rec != null)
                        {
                            if (rec.ProviderNarrative != null)
                            {
                                rec.ProviderNarrative = rec.ProviderNarrative.Substring(0, rec.ProviderNarrative.Length - 1);
                            }
                            if (rec.NoteNarrative != null)
                            {
                                rec.NoteNarrative = rec.NoteNarrative.Substring(0, rec.NoteNarrative.Length - 1);
                            }
                            if (rec.Exposures != null)
                            {
                                rec.Exposures = rec.Exposures.Substring(0, rec.Exposures.Length - 1);
                            }
                            lst.Add(rec);
                        }
                        rec = new Problem();
                        rec.Type = new ObservationType("", "Problems and Diagnoses", "Problem");
                        string[] parts = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                        if (parts.Length == 2)
                        {
                            rec.Facility = new SiteId(parts[0], parts[1]);
                        }
                        else if (flds[1] != "")
                        {
                            rec.Facility = new SiteId(cxn.DataSource.SiteId.Id, flds[1]);
                        }
                        else
                        {
                            rec.Facility = cxn.DataSource.SiteId;
                        }
                        break;
                    case 2:
                        if (flds.Length == 2)
                        {
                            rec.Status = flds[1];
                        }
                        break;
                    case 3:
                        if (flds.Length == 2)
                        {
                            rec.ProviderNarrative += flds[1] + '\n';
                        }
                        break;
                    case 4:
                        if (flds.Length == 2)
                        {
                            rec.OnsetDate = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 5:
                        if (flds.Length == 2)
                        {
                            rec.ModifiedDate = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 6:
                        if (flds.Length == 2)
                        {
                            rec.Observer = new Author("",flds[1],"");
                        }
                        break;
                    case 7:
                        if (flds.Length == 2)
                        {
                            rec.NoteNarrative += flds[1] + '\n';
                        }
                        break;
                    case 8:
                        if (flds.Length == 2)
                        {
                            rec.Exposures += flds[1] + '\n';
                        }
                        break;
                }
            }
            if (rec != null)
            {
                if (rec.ProviderNarrative != null)
                {
                    rec.ProviderNarrative = rec.ProviderNarrative.Substring(0, rec.ProviderNarrative.Length - 1);
                }
                if (rec.NoteNarrative != null)
                {
                    rec.NoteNarrative = rec.NoteNarrative.Substring(0, rec.NoteNarrative.Length - 1);
                }
                if (rec.Exposures != null)
                {
                    rec.Exposures = rec.Exposures.Substring(0, rec.Exposures.Length - 1);
                }
                lst.Add(rec);
            }
            Problem[] result = (Problem[])lst.ToArray(typeof(Problem));
            //annotateAcuteProblems(rex, type, dfn);
            return result;
        }

        public string[] getProblems()
        {
            return getProblems(cxn.Pid);
        }

        public string[] getProblems(string dfn)
        {
            MdoQuery request = buildGetProblemsRequest(dfn);
            string response = (string)cxn.query(request);
            return toProblems(response);
        }

        internal MdoQuery buildGetProblemsRequest(string dfn)
        {
            VistaQuery vq = new VistaQuery("ORQQPL PROBLEM LIST");
            vq.addParameter(vq.LITERAL, dfn);
            return vq;
        }

        internal string[] toProblems(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] rex = StringUtils.split(response, StringUtils.CRLF);
            rex = StringUtils.trimArray(rex);
            string[] result = new string[rex.Length - 1];
            Array.Copy(rex,1,result,0,rex.Length-1);
            return result;
        }

        #endregion

        #region Surgery

        public SurgeryReport[] getSurgeryReports(bool fWithText)
        {
            return getSurgeryReports(cxn.Pid, fWithText);
        }

        public SurgeryReport[] getSurgeryReports(string dfn, bool fWithText)
        {
            MdoQuery request = buildGetSurgeryReportsRequest(dfn);
            string response = (string)cxn.query(request);
            return toSurgeryReports(response, fWithText);
        }

        internal MdoQuery buildGetSurgeryReportsRequest(string dfn)
        {
            VistaQuery vq = new VistaQuery("ORWSR RPTLIST");
            vq.addParameter(vq.LITERAL, dfn);
            return vq;
        }

        internal SurgeryReport[] toSurgeryReports(string response, bool fWithText)
        {
            if (response == "")
            {
                return null;
            }
            ArrayList lst = new ArrayList();
            SurgeryReport rec = null;
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i] == "")
                {
                    continue;
                }
			    string[] flds = StringUtils.split(lines[i],StringUtils.CARET);
			    rec = new SurgeryReport();
                string[] parts = StringUtils.split(flds[0], StringUtils.SEMICOLON);
                rec.Facility = new SiteId(parts[0], parts[1]);
			    rec.Id = flds[1];
			    rec.Timestamp = flds[2];
			    rec.Title = flds[3];
			    rec.Author = new Author("",flds[4],"");
                if (fWithText)
                {
                    rec.Text = getSurgeryReportText(cxn.Pid, rec.Id);
                }
			    lst.Add(rec);
		    }
		    return (SurgeryReport[])lst.ToArray(typeof(SurgeryReport));
        }

        public string getSurgeryReportText(string ien)
        {
            return getSurgeryReportText(cxn.Pid, ien);
        }

        public string getSurgeryReportText(string dfn, string ien)
        {
            MdoQuery request = buildGetSurgeryReportTextRequest(dfn, ien);
            string response = (string)cxn.query(request);
            return response;
        }

        public MdoQuery buildGetSurgeryReportTextRequest(string dfn, string ien)
        {
            VistaQuery vq = new VistaQuery("ORWRP REPORT TEXT");
            vq.addParameter(vq.LITERAL, dfn);
            vq.addParameter(vq.LITERAL, "28:SURGERY (LOCAL ONLY)~");
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, "");
            vq.addParameter(vq.LITERAL, ien);
            vq.addParameter(vq.LITERAL, "0");
            vq.addParameter(vq.LITERAL, "0");
            return vq;
        }

        public SurgeryReport[] getSurgeryReportsBySite(string siteCode)
        {
            return getSurgeryReports(true);
        }

        #endregion

        #region NHIN/AViVA Call
        /// <summary>
        /// Get NHIN data
        /// </summary>
        /// <param name="types">The RPC argument for data types</param>
        /// <returns>A string of NHIN data in XML format</returns>
        public string getNhinData(string types)
        {
            return getNhinData(cxn.Pid, types, null);
        }

        /// <summary>
        /// Get NHIN data
        /// </summary>
        /// <param name="types">The RPC argument for data types</param>
        /// <param name="validTypesString">Semicolon delimited valid types string</param>
        /// <returns>A string of NHIN data in XML format</returns>
        public string getNhinData(string types, string validTypesString)
        {
            return getNhinData(cxn.Pid, types, types.Split(new char[] { ';' }));
        }

        /// <summary>
        /// Get NHIN data
        /// </summary>
        /// <param name="types">The RPC argument for data types</param>
        /// <param name="validTypes">A string array of valid types</param>
        /// <returns>A string of NHIN data in XML format</returns>
        public string getNhinData(string types, string[] validTypes)
        {
            return getNhinData(cxn.Pid, types, validTypes);
        }

        //Added the menu option back in for the nhin context.
        internal string getNhinData(string dfn, string types, string[] validTypes)
        {
            MdoQuery request = buildGetNhinData(dfn, types, validTypes);
            string response = (string)cxn.query(request , new MenuOption(VistaConstants.VPR_CONTEXT) );
            return response;
        }

        internal MdoQuery buildGetNhinData(string dfn, string types, string[] validTypes)
        {
            VistaUtils.CheckRpcParams(dfn);
            if (validTypes != null && !isValidTypesString(types, validTypes))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid types string");
            }
            VistaQuery vq = new VistaQuery("VPR GET PATIENT DATA");
            vq.addParameter(vq.LITERAL, dfn);
            vq.addParameter(vq.LITERAL, types);
            vq.addParameter(vq.LITERAL, ""); // start
            vq.addParameter(vq.LITERAL, ""); // stop
            vq.addParameter(vq.LITERAL, ""); // max
            vq.addParameter(vq.LITERAL, ""); // id
            DictionaryHashList paramsLst = new DictionaryHashList();
            paramsLst.Add("\"text\"", "1");
            vq.addParameter(vq.LIST, paramsLst); // filter
            return vq;
        }

        internal bool isValidTypesString(string types, string[] validTypes)
        {
            if (String.IsNullOrEmpty(types))
            {
                return true;
            }
            // found in app.config / web.config for dynamic reloading, 
            // if adding a type, add it here and to each app.config and the web.config
            /* string[] validTypeArray = 
            { 
                "accession",
                "allergy", 
                "appointment",
                "document",
                "immunization",
                "lab", 
                "med",
                "panel",
                "patient", 
                "problem",
                "procedure",
                "radiology",
                "rx",
                "surgery",
                "visit",
                "vital"
            };
             * */

            HashSet<string> validTypesSet = new HashSet<string>(validTypes); 

            string requestedTypes = types.ToLower().Replace(" ", "");
            HashSet<string> requestedTypesSet = new HashSet<string>(requestedTypes.ToLower().Split(';'));

            return requestedTypesSet.IsSubsetOf(validTypesSet);            
        }

        public Hashtable getPatientRecord(string types)
        {
            return getPatientRecord(cxn.Pid, types);
        }

        public Hashtable getPatientRecord(string patientID, string types)
        {
            if (string.IsNullOrEmpty(patientID))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL_PATIENT_ID);
            }
            cxn.Pid = patientID; // need to be sure this is set because of the supplemental calls to others DAOs for data

            // ticket #3046
            if (!String.IsNullOrEmpty(types)) //if we were not passed any types, demogs will be fetched anyways
            {
                if (!types.ToLower().Contains("demographics"))
                {
                    types = String.Concat(types, ";demographics");
                }
            }
            //
            // ticket 3051
            //if (!String.IsNullOrEmpty(types))
            //{
            //    if (types.ToLower().Contains("accession"))
            //    {
            //        types = String.Concat("panels;", types);
            //    }
            //}
            // end 3051
            MdoQuery request = buildGetNhinData(patientID, types, null);
            string response = (string)cxn.query(request, new MenuOption(VistaConstants.VPR_CONTEXT));
            return parseVprXml(response, types.Split(new char[1] { ';' })); 
        }

        // Temporary note - the types passed in here do not correspond to the valid types collection. Demographics, reactions, problems, etc are
        // the types we will expect at the top level - those will contain collections of the valid types we are familiar with
        internal Hashtable parseVprXml(string xml, string[] types)
        {
            xml = StringUtils.stripInvalidXmlCharacters(xml);

            XmlDocument xmlDoc = new XmlDocument();
            xmlDoc.LoadXml(xml);

            Patient patient = new Patient();
            IList<Allergy> allergies = new List<Allergy>();
            IList<Appointment> appointments = new List<Appointment>();
            IList<Note> notes = new List<Note>();
            IList<LabReport> labs = new List<LabReport>();
            IList<LabReport> accessions = new List<LabReport>();
            IList<Medication> meds = new List<Medication>();
            IList<PathologyReport> pathReports = new List<PathologyReport>();
            IList<Problem> problems = new List<Problem>();
            IList<ImagingExam> radiologyReports = new List<ImagingExam>();
            IList<SurgeryReport> surgeryReports = new List<SurgeryReport>();
            IList<Visit> visits = new List<Visit>();
            IList<VitalSignSet> vitals = new List<VitalSignSet>();
            IList<Consult> consults = new List<Consult>();
            IList<HealthSummary> healthSummaries = new List<HealthSummary>();
            IList<PatientRecordFlag> flags = new List<PatientRecordFlag>();
            IList<Immunization> immunizations = new List<Immunization>();
            IList<Note> dischargeSummaries = new List<Note>();
            IList<ClinicalProcedure> EKGs = new List<ClinicalProcedure>();
            //Immunizations immunizations = new Immunizations() { Immunization = new List<StructuredProductType>() };

            foreach (string type in types)
            {
                // we were checking to make sure there was XML for each type but turns out for some domains that aren't in VPR (dischargeSummaries for example)
                // there is no XML so it doesn't make sense to do the verification - leave that up to the toDomain functions
                //XmlNodeList nodes = xmlDoc.SelectSingleNode("/results/" + type); //.GetElementsByTagName("/results/" + type);
                //XmlNode node = null;
                //if (nodes != null && nodes.Count > 0)
                //{
                //    node = nodes[0]; // should only be one for each of the expected types
                //}

                XmlNode node = xmlDoc.SelectSingleNode("/results/" + type);

                // finally we know we have some data to parse
                switch (type)
                {
                    case "ekgs" :
                        EKGs = toEkgsFromXmlNode(node);
                        break;
                    case "demographics":            // ticket #3046
                        patient = new VistaPatientDao(this.cxn).selectWithDdrGetsEntry(this.cxn.Pid); // toPatientFromXmlNode(node);
                        break;
                    case "reactions" :
                        allergies = toAllergiesFromXmlNode(node);
                        break;
                    case "problems" :
                        problems = toProblemsFromXmlNode(node);
                        break;
                    case "vitals" :
                        vitals = toVitalsFromXmlNode(node);
                        break;
                    case "accessions":
                        //XmlNode supplementalNode = xmlDoc.SelectSingleNode("/results/panels");
                        //accessions = toAccessionsFromXmlNode(node, supplementalNode);
                        accessions = toAccessionsFromXmlNode(node);
                        break;
                    case "labs":
                        labs = toLabsFromXmlNode(node);
                        break;
                    case "meds":
                        meds = (IList<Medication>)toMedsFromXmlNode(node, typeof(IList<Medication>));
                        break;
                    case "immunizations" :
                        immunizations = (IList<Immunization>)toImmunizationsFromXmlNode(node, typeof(IList<Immunization>));
                        break;
                    case "appointments" :
                        appointments = toAppointmentsFromXmlNode(node);
                        break;
                    case "visits" :
                        visits = toVisitsFromXmlNode(node);
                        break;
                    case "documents" :
                        notes = toNotesFromXmlNode(node);
                        break;
                    case "procedures" :
                        // TODO - need to implement
                        break;
                    case "consults" :
                        consults = toConsultsFromXmlNode(node);
                        break;
                    case "flags" :
                        flags = toFlagsFromXmlNode(node);
                        break;
                    case "healthFactors" :
                        healthSummaries = toHealthSummariesFromXmlNode(node);
                        break;
                    case "radiologyExams" :
                        radiologyReports = toImagingExamsFromXmlNode(node);
                        break;
                    case "dischargeSummaries" :
                        dischargeSummaries = toDischargeSummariesFromXmlNode();
                        break;

                }
            }

            Hashtable results = new Hashtable();
            results.Add("ekgs", EKGs);
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
            results.Add("accessions", accessions);
            results.Add("labs", labs);
            results.Add("meds", meds);
            results.Add("immunizations", immunizations);
            results.Add("radiologyExams", radiologyReports);
            results.Add("dischargeSummaries", dischargeSummaries);

            return results;
        }

        private IList<ClinicalProcedure> toEkgsFromXmlNode(XmlNode node)
        {
            return new VistaProceduresDao(this.cxn).getClinicalProceduresWithText(new List<string>() { "ELECTROCARDIOGRAM" });
        }

        // bit of a misnomer - VPR doesn't have this domain so an XML node is never passed in
        private IList<Note> toDischargeSummariesFromXmlNode()
        {
            VistaNoteDao noteDao = new VistaNoteDao(this.cxn);
            Note[] dischargeSummaries = noteDao.getDischargeSummaries("", "", 10000);
            IList<Note> result = new List<Note>();
            if (dischargeSummaries != null && dischargeSummaries.Length > 0)
            {
                for (int i = 0; i < dischargeSummaries.Length; i++)
                {
                    result.Add(dischargeSummaries[i]);
                } 
            }
            return result;
        }

        #region VPR XML Parsing

        int verifyTopLevelNode(XmlNode node)
        {
            if (node == null || node.Attributes == null || node.Attributes.Count == 0 || node.Attributes["total"] == null)
            {
                return 0;
            }
            string strTotal = node.Attributes["total"].Value;
            int total = 0;
            if (!Int32.TryParse(strTotal, out total))
            {
                return 0;
            }
            return total;
        }

        internal IList<ImagingExam> toImagingExamsFromXmlNode(XmlNode node)
        {
            IList<ImagingExam> radiology = new List<ImagingExam>();

            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return radiology;
            }

            XmlNodeList radiologyNodes = node.SelectNodes("radiology");
            if (radiologyNodes == null || radiologyNodes.Count == 0)
            {
                return radiology;
            }

            foreach (XmlNode radiologyNode in radiologyNodes)
            {
                // This data is now going to be supplemented by a call to getRadiologyReports
                // notes multiple
                //IList<RadiologyReport> notes = new List<RadiologyReport>();
                //XmlNodeList notesNodes = radiologyNode.SelectNodes("documents/document");
                //if (notesNodes != null && notesNodes.Count > 0)
                //{
                //    foreach (XmlNode noteNode in notesNodes)
                //    {
                //        string noteId = XmlUtils.getXmlAttributeValue(noteNode, "/", "id");
                //        string localTitle = XmlUtils.getXmlAttributeValue(noteNode, "/", "localTitle");
                //        string noteStatus = XmlUtils.getXmlAttributeValue(noteNode, "/", "status");
                //        notes.Add(new RadiologyReport() { Id = noteId, Title = localTitle, Status = noteStatus });
                //    }
                //}
                // end notes

                // modifiers multiple
                IList<CptCode> modifiers = new List<CptCode>();
                XmlNodeList modifiersNodes = radiologyNode.SelectNodes("modifiers/modifier");
                if (modifiersNodes != null && modifiersNodes.Count > 0)
                {
                    foreach (XmlNode modifierNode in modifiersNodes)
                    {
                        string code = XmlUtils.getXmlAttributeValue(modifierNode, "/", "code");
                        string modifierName = XmlUtils.getXmlAttributeValue(modifierNode, "/", "name");
                        modifiers.Add(new CptCode() { Id = code, Name = modifierName });
                    }
                }
                // end modifiers

                string caseId = XmlUtils.getXmlAttributeValue(radiologyNode, "case", "value");
                string category = XmlUtils.getXmlAttributeValue(radiologyNode, "category", "value");
                string timestamp = XmlUtils.getXmlAttributeValue(radiologyNode, "dateTime", "value");
                string encounter = XmlUtils.getXmlAttributeValue(radiologyNode, "encounter", "value");
                KeyValuePair<string, string> facility = new KeyValuePair<string,string>
                    (XmlUtils.getXmlAttributeValue(radiologyNode, "facility", "code"), XmlUtils.getXmlAttributeValue(radiologyNode, "facility", "name"));
                bool hasImages = (XmlUtils.getXmlAttributeValue(radiologyNode, "hasImages", "value") == "1");
                string id = XmlUtils.getXmlAttributeValue(radiologyNode, "id", "value");
                KeyValuePair<string, string> imagingType = new KeyValuePair<string, string>
                    (XmlUtils.getXmlAttributeValue(radiologyNode, "imagingType", "code"), XmlUtils.getXmlAttributeValue(radiologyNode, "imagingType", "name"));
                string interpretation = XmlUtils.getXmlAttributeValue(radiologyNode, "interpretation", "value");
                KeyValuePair<string, string> hospitalLocation = new KeyValuePair<string, string>
                    (XmlUtils.getXmlAttributeValue(radiologyNode, "location", "code"), XmlUtils.getXmlAttributeValue(radiologyNode, "location", "name"));
                string name = XmlUtils.getXmlAttributeValue(radiologyNode, "name", "value");
                KeyValuePair<string, string> order = new KeyValuePair<string, string>
                    (XmlUtils.getXmlAttributeValue(radiologyNode, "order", "code"), XmlUtils.getXmlAttributeValue(radiologyNode, "order", "name"));
                KeyValuePair<string, string> provider = new KeyValuePair<string, string>
                    (XmlUtils.getXmlAttributeValue(radiologyNode, "provider", "code"), XmlUtils.getXmlAttributeValue(radiologyNode, "provider", "name"));
                string status = XmlUtils.getXmlAttributeValue(radiologyNode, "status", "value");
                KeyValuePair<string, string> type = new KeyValuePair<string, string>
                    (XmlUtils.getXmlAttributeValue(radiologyNode, "type", "code"), XmlUtils.getXmlAttributeValue(radiologyNode, "type", "name"));

                ImagingExam exam = new ImagingExam()
                {
                    CaseNumber = caseId,
                    Encounter = new Encounter() { Id = encounter, Type = category },
                    HasImages = hasImages,
                    Id = id,
                    ImagingType = imagingType.Value,
                    Interpretation = interpretation,
                    ImagingLocation = new HospitalLocation(hospitalLocation.Key, hospitalLocation.Value),
                    Modifiers = modifiers,
                    Name = name,
                    Order = new Order() { Id = order.Key, Type = new OrderType() { Name1 = order.Value } },
                    Provider = new User() { Id = provider.Key, Name = new PersonName(provider.Value) },
                    Status = status,
                    Timestamp = timestamp,
                    Type = new CptCode() { Id = type.Key, Name = type.Value }
                };

                radiology.Add(exam);
            }

            // supplement reports
            VistaRadiologyDao radioDao = new VistaRadiologyDao(cxn);
            RadiologyReport[] rpts = radioDao.getRadiologyReports("", "", 10000);

            foreach (ImagingExam exam in radiology)
            {
                foreach (RadiologyReport rpt in rpts)
                {
                    if (String.Equals(rpt.Id, exam.Id))
                    {
                        if (exam.Reports == null || exam.Reports.Count == 0)
                        {
                            exam.Reports = new List<RadiologyReport>();
                        }
                        exam.Reports.Add(rpt);
                        // don't break - could be more than one report for an exam
                    }
                }
            }
            // end supplement

            // supplemental order info
            //VistaOrdersDao ordersDao = new VistaOrdersDao(this.cxn);
            //Order[] patientOrders = ordersDao.getOrders(this.cxn.Pid);
            //if (patientOrders != null && patientOrders.Length > 0)
            //{
            //    foreach (Order order in patientOrders)
            //    {
            //        foreach (ImagingExam exam in radiology)
            //        {
            //            if (exam.Order == null || String.IsNullOrEmpty(exam.Order.Id))
            //            {
            //                continue;
            //            }
            //            if (String.Equals(order.Id, exam.Order.Id))
            //            {
            //                exam.Order = order;
            //                break;
            //            }
            //        }
            //    }
            //}
            // end supplement

            return radiology;
        }

        internal object toImmunizationsFromXmlNode(XmlNode node, Type returnType)
        {
            if (!(returnType == typeof(Immunizations)) && !(returnType == typeof(IList<Immunization>)))
            {
                throw new MdoException("Current valid return types are CCR object Immunizations and IList<Immunization>");
            }

            object result = null;

            if (returnType == typeof(Immunizations))
            {
                Immunizations imm = new Immunizations();
                imm.Immunization = new List<StructuredProductType>();
                result = imm;
            }
            else if (returnType == typeof(IList<Immunization>))
            {
                result = new List<Immunization>();
            }
            else
            {
                // TBD - implement other immunization type? probably should throw error
            }

            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return result;
            }

            XmlNodeList immNodes = node.SelectNodes("immunization");
            if (immNodes == null || immNodes.Count == 0)
            {
                return result;
            }

            CCRHelper helper = new CCRHelper();

            foreach (XmlNode immNode in immNodes)
            {
                string timestamp = XmlUtils.getXmlAttributeValue(immNode, "administered", "value");
                string comments = XmlUtils.getXmlAttributeValue(immNode, "comment", "value");
                string contraindicated = XmlUtils.getXmlAttributeValue(immNode, "contraindicated", "value");
                string cptCode = XmlUtils.getXmlAttributeValue(immNode, "cpt", "code");
                string cptValue = XmlUtils.getXmlAttributeValue(immNode, "cpt", "name");
                string encounter = XmlUtils.getXmlAttributeValue(immNode, "encounter", "value");
                string location = XmlUtils.getXmlAttributeValue(immNode, "location", "value");
                string facilityId = XmlUtils.getXmlAttributeValue(immNode, "facility", "code");
                string facilityName = XmlUtils.getXmlAttributeValue(immNode, "facility", "name");
                string immIen = XmlUtils.getXmlAttributeValue(immNode, "id", "value");
                string name = XmlUtils.getXmlAttributeValue(immNode, "name", "value");
                string reaction = XmlUtils.getXmlAttributeValue(immNode, "reaction", "value");
                string series = XmlUtils.getXmlAttributeValue(immNode, "series", "value");
                string administratorId = XmlUtils.getXmlAttributeValue(immNode, "provider", "code");
                string administratorName = XmlUtils.getXmlAttributeValue(immNode, "provider", "name");

                if (returnType == typeof(Immunizations))
                {
                    StructuredProductType imm = helper.buildImmunizationObject(immIen, timestamp, contraindicated, encounter, facilityName, name, reaction);
                    ((Immunizations)result).Immunization.Add(imm);
                }
                else if (returnType == typeof(IList<Immunization>))
                {
                    Immunization imm = new Immunization();
                    imm.AdministeredDate = timestamp;
                    imm.Comments = comments;
                    imm.Contraindicated = contraindicated;
                    imm.Encounter = new Visit()
                    {
                        VisitId = encounter,
                        Provider = new User() { Id = administratorId, Name = new PersonName(administratorName) },
                        Location = new HospitalLocation("", location),
                        Facility = new SiteId(facilityId, facilityName)
                    };
                    imm.Id = immIen;
                    imm.CptCode = new CptCode() { Id = cptCode, Name = cptValue };
                    imm.Name = name;
                    imm.Reaction = reaction;
                    imm.Series = series;

                    ((IList<Immunization>)result).Add(imm);
                }
                else
                {
                    // needed? throw exception?
                }
            }

            return result;
        }

        /// <summary>
        /// Currently, this function is serving only as a supplement to the accession view. All fields are NOT being mapped
        /// </summary>
        internal Dictionary<string, LabReport> toPanelsFromXmlNode(XmlNode node)
        {
            Dictionary<String, LabReport> labs = new Dictionary<string, LabReport>();
            
            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return labs;
            }

            XmlNodeList labNodes = node.SelectNodes("panel");
            if (labNodes == null || labNodes.Count == 0)
            {
                return labs;
            }

            foreach (XmlNode labNode in labNodes)
            {
                string accessionNumber = XmlUtils.getXmlAttributeValue(labNode, "groupName", "value");

                if (String.IsNullOrEmpty(accessionNumber))
                {
                    continue;
                }

                string orderId = XmlUtils.getXmlAttributeValue(labNode, "order", "code");
                string orderName = XmlUtils.getXmlAttributeValue(labNode, "order", "name");

                LabReport rpt = new LabReport();
                rpt.Panel = new LabPanel() { Name = orderName };

                if (!labs.ContainsKey(accessionNumber))
                {
                    labs.Add(accessionNumber, rpt);
                }
            }

            return labs;
        }

        internal IList<LabReport> toAccessionsFromXmlNode(XmlNode node)
        {
            return toAccessionsFromXmlNode(node, null);
        }

        internal IList<LabReport> toAccessionsFromXmlNode(XmlNode node, XmlNode supplementalNode)
        {
            IList<LabReport> labs = new List<LabReport>();

            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return labs;
            }

            XmlNodeList labNodes = node.SelectNodes("accession");
            if (labNodes == null || labNodes.Count == 0)
            {
                return labs;
            }

            //Dictionary<string, LabReport> supplement = toPanelsFromXmlNode(supplementalNode);

            foreach (XmlNode labNode in labNodes)
            {
                LabReport current = new LabReport();
                current.Panel = new LabPanel();

                XmlNode commentNode = labNode.SelectSingleNode("comment");
                string comment = "";
                if (commentNode != null)
                {
                    comment = commentNode.InnerXml;
                }

                Note labNote = null;
                XmlNodeList noteNodes = labNode.SelectNodes("documents/document");
                if (noteNodes != null && noteNodes.Count > 0)
                {
                    foreach (XmlNode noteNode in noteNodes)
                    {
                        string noteText = noteNode.InnerText;
                        string noteId = XmlUtils.getXmlAttributeValue(noteNode, "/", "id");
                        string localNoteTitle = XmlUtils.getXmlAttributeValue(noteNode, "/", "localTitle");
                        string nationalNoteTitle = XmlUtils.getXmlAttributeValue(noteNode, "/", "nationalTitle");

                        labNote = new Note()
                        {
                            Id = noteId,
                            LocalTitle = localNoteTitle,
                            StandardTitle = nationalNoteTitle,
                            Text = noteText
                        };
                    }
                }
                
                string collected = XmlUtils.getXmlAttributeValue(labNode, "collected", "value");
                // document - TODO
                string accessionNumber = XmlUtils.getXmlAttributeValue(labNode, "groupName", "value");
                KeyValuePair<string, string> facility = new KeyValuePair<string, string>
                    (XmlUtils.getXmlAttributeValue(labNode, "facility", "code"), XmlUtils.getXmlAttributeValue(labNode, "facility", "name"));
                string id = XmlUtils.getXmlAttributeValue(labNode, "id", "value");
                string labOrderId = XmlUtils.getXmlAttributeValue(labNode, "labOrderID", "value");
                string name = XmlUtils.getXmlAttributeValue(labNode, "name", "value");
                string resultDate = XmlUtils.getXmlAttributeValue(labNode, "resulted", "value");
                string sample = XmlUtils.getXmlAttributeValue(labNode, "sample", "value");
                string specimenCode = XmlUtils.getXmlAttributeValue(labNode, "specimen", "code");
                string specimenType = XmlUtils.getXmlAttributeValue(labNode, "specimen", "name");
                string status = XmlUtils.getXmlAttributeValue(labNode, "status", "value");
                string testDataType = XmlUtils.getXmlAttributeValue(labNode, "type", "value"); // CH, MI, CY, EM, SP, AU, BB

                XmlNodeList testNodes = labNode.SelectNodes("values/value");
                if (testNodes != null && testNodes.Count > 0)
                {
                    current.Panel.Tests = new List<LabTest>();
                    foreach (XmlNode testNode in testNodes)
                    {
                        string testHighRef = XmlUtils.getXmlAttributeValue(testNode, "/", "high");
                        string testLowRef = XmlUtils.getXmlAttributeValue(testNode, "/", "low");
                        string testName = XmlUtils.getXmlAttributeValue(testNode, "/", "test");
                        string testUnits = XmlUtils.getXmlAttributeValue(testNode, "/", "units");
                        string vuid = XmlUtils.getXmlAttributeValue(testNode, "/", "vuid");
                        string testId = XmlUtils.getXmlAttributeValue(testNode, "/", "id");
                        string interpretation = XmlUtils.getXmlAttributeValue(testNode, "/", "interpretation");
                        string resultValue = XmlUtils.getXmlAttributeValue(testNode, "/", "result");
                        string testLoinc = XmlUtils.getXmlAttributeValue(testNode, "/", "loinc");
                        string testShortName = XmlUtils.getXmlAttributeValue(testNode, "/", "localName");

                        LabTest newTest = new LabTest()
                        {
                            DataName = testName,
                            HiRef = testHighRef,
                            Id = testId,
                            Loinc = testLoinc,
                            LowRef = testLowRef,
                            Name = testName,
                            Result = new LabResult() 
                            { 
                                BoundaryStatus = interpretation, 
                                Value = resultValue, 
                            },
                            Units = testUnits
                        };

                        current.Panel.Tests.Add(newTest);
                    }
                }

                LabSpecimen specimen = new LabSpecimen(specimenCode, specimenType, collected, accessionNumber);
                current.Specimen = specimen;

                current.Result = new LabResult()
                {
                    Timestamp = resultDate,
                    Comment = comment,
                    LabSiteId = facility.Key,
                };

                current.Type = testDataType;
                current.Id = id;
                if (labNote != null)
                {
                    current.Title = labNote.LocalTitle;
                    current.Text = labNote.Text;
                }
                else
                {
                    current.Title = name;
                }

                labs.Add(current);
            }

            // supplement
            Dictionary<string, IList<LabReport>> labsToSupplement = new Dictionary<string,IList<LabReport>>();
            foreach (LabReport rpt in labs)
            {
                if (String.IsNullOrEmpty(rpt.Type))
                {
                    continue;
                }
                if (!labsToSupplement.ContainsKey(rpt.Type))
                {
                    labsToSupplement.Add(rpt.Type, new List<LabReport>());
                }
                labsToSupplement[rpt.Type].Add(rpt);
            }

            supplementElectronMicroscopyLabs(labsToSupplement);
            // end supplement

            return labs;
        }

        private void supplementBloodBankLabs(Dictionary<string, IList<LabReport>> labsByType)
        {
            if (!labsByType.ContainsKey("BB"))
            {
                return;
            }
            string report = new VistaLabsDao(this.cxn).getBloodBankReport(cxn.Pid);
            // TBD - what to do? all BB calls appear to return a single report
        }

        private void supplementAutopsyLabs(Dictionary<string, IList<LabReport>> labsByType)
        {
            if (!labsByType.ContainsKey("AU")) // TBD: should this be "AY"?? - need some records...
            {
                return;
            }
            string report = new VistaLabsDao(this.cxn).getAutopsyReport(cxn.Pid);
            // TBD - what to do? all BB calls appear to return a single report
        }

        private void supplementCytologyLabs(Dictionary<string, IList<LabReport>> labsByType)
        {
            if (!labsByType.ContainsKey("CY"))
            {
                return;
            }
            CytologyReport[] rpts = new VistaLabsDao(this.cxn).getCytologyReports(cxn.Pid, "", "", 10000);
        }

        private void supplementElectronMicroscopyLabs(Dictionary<string, IList<LabReport>> labsByType)
        {
            if (!labsByType.ContainsKey("EM"))
            {
                return;
            }
            ElectronMicroscopyReport[] rpts = new VistaLabsDao(cxn).getElectronMicroscopyReports(cxn.Pid, "", "", 10000);
            if (rpts != null && rpts.Length > 0)
            {
                for (int i = 0; i < rpts.Length; i++)
                {
                    if (rpts[i].Specimen == null || String.IsNullOrEmpty(rpts[i].Specimen.AccessionNumber))
                    {
                        continue;
                    }
                    for (int j = 0; j < labsByType["EM"].Count; j++)
                    {
                        if (labsByType["EM"][j].Specimen == null || String.IsNullOrEmpty(labsByType["EM"][j].Specimen.AccessionNumber))
                        {
                            continue;
                        }
                        if (labsByType["EM"][j].Specimen.AccessionNumber.Contains(rpts[i].Specimen.AccessionNumber))
                        {
                            labsByType["EM"][j].Text = rpts[i].Exam;
                            break;
                        }
                    }
                }
            }
        }

        private void supplementSurgicalPathologyLabs(Dictionary<string, IList<LabReport>> labsByType)
        {
            if (!labsByType.ContainsKey("SP"))
            {
                return;
            }
            SurgicalPathologyReport[] rpts = new VistaLabsDao(this.cxn).getSurgicalPathologyReports(cxn.Pid, "", "", 10000);

            if (rpts != null && rpts.Length > 0)
            {
                for (int i = 0; i < rpts.Length; i++)
                {
                    if (rpts[i].Specimen == null || String.IsNullOrEmpty(rpts[i].Specimen.AccessionNumber))
                    {
                        continue;
                    }
                    for (int j = 0; j < labsByType["SP"].Count; j++)
                    {
                        if (labsByType["SP"][j].Specimen == null || String.IsNullOrEmpty(labsByType["SP"][j].Specimen.AccessionNumber))
                        {
                            continue;
                        }
                        if (labsByType["SP"][j].Specimen.AccessionNumber.Contains(rpts[i].Specimen.AccessionNumber))
                        {
                            labsByType["SP"][j].Text = rpts[i].Exam;
                            break;
                        }
                    }
                }
            }

        }

        private void supplementMicroLabs(Dictionary<string, IList<LabReport>> labsByType)
        {
            if (!labsByType.ContainsKey("MI"))
            {
                return;
            }
            MicrobiologyReport[] rpts = new VistaLabsDao(this.cxn).getMicrobiologyReports(cxn.Pid, "", "", 10000);
            if (rpts != null && rpts.Length > 0)
            {
                for (int i = 0; i < rpts.Length; i++)
                {
                    if (rpts[i].Specimen == null || String.IsNullOrEmpty(rpts[i].Specimen.AccessionNumber))
                    {
                        continue;
                    }
                    for (int j = 0; j < labsByType["MI"].Count; j++)
                    {
                        if (labsByType["MI"][j].Specimen == null || String.IsNullOrEmpty(labsByType["MI"][j].Specimen.AccessionNumber))
                        {
                            continue;
                        }
                        if (labsByType["MI"][j].Specimen.AccessionNumber.Contains(rpts[i].Specimen.AccessionNumber))
                        {
                            labsByType["MI"][j].Text = rpts[i].Text;
                            break;
                        }
                    }
                }
            }
        }

        void supplementChemHemLabs(Dictionary<string, IList<LabReport>> labsByType)
        {
            return; // TODO - finish!!! the below does not work
            if (!labsByType.ContainsKey("CH"))
            {
                return;
            }
            ChemHemReport[] rpts = new VistaChemHemDao(this.cxn).getChemHemReports(cxn.Pid, "", "", 10000);
            if (rpts != null && rpts.Length > 0)
            {
                for (int i = 0; i < rpts.Length; i++)
                {
                    if (String.IsNullOrEmpty(rpts[i].Timestamp))
                    {
                        continue;
                    }
                    for (int j = 0; j < labsByType["CH"].Count; j++)
                    {
                        if (labsByType["CH"][j].Result == null || String.IsNullOrEmpty(labsByType["CH"][j].Result.Timestamp))
                        {
                            continue;
                        }
                        string rptUtc = VistaTimestamp.fromUtcString(rpts[1].Timestamp);
                        string labsByTypeUtc = labsByType["CH"][j].Specimen.CollectionDate;
                        //19991225.073000        //2991225.073000
                        if (labsByTypeUtc.Equals(rptUtc))
                        {
                            labsByType["CH"][j].Text = rpts[i].Comment; // the getChemHemReports supplemental call stores the report in the comment fiels
                            break;
                        }
                    }
                }
            }
        }

        // TODO - finish and write tests
        internal IList<LabReport> toLabsFromXmlNode(XmlNode node)
        {
            IList<LabReport> labs = new List<LabReport>();

            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return labs;
            }

            XmlNodeList labNodes = node.SelectNodes("lab");
            if (labNodes == null || labNodes.Count == 0)
            {
                return labs;
            }

            //CCRHelper helper = new CCRHelper();
            Dictionary<string, LabReport> results = new Dictionary<string, LabReport>();

            foreach (XmlNode labNode in labNodes)
            {
                XmlNode commentNode = labNode.SelectSingleNode("comment");
                string comment = "";
                if (commentNode != null)
                {
                    comment = commentNode.InnerXml;
                }

                string collected = XmlUtils.getXmlAttributeValue(labNode, "collected", "value");
                KeyValuePair<string, string> facility = new KeyValuePair<string, string>
                    (XmlUtils.getXmlAttributeValue(labNode, "facility", "code"), XmlUtils.getXmlAttributeValue(labNode, "facility", "name"));
                string accessionNumber = XmlUtils.getXmlAttributeValue(labNode, "groupName", "value");
                string testHighRef = XmlUtils.getXmlAttributeValue(labNode, "high", "value");
                string id = XmlUtils.getXmlAttributeValue(labNode, "id", "value");
                string interpretation = XmlUtils.getXmlAttributeValue(labNode, "interpretation", "value");
                string testShortName = XmlUtils.getXmlAttributeValue(labNode, "localName", "value");
                string testLoinc = XmlUtils.getXmlAttributeValue(labNode, "loinc", "value");
                string testLowRef = XmlUtils.getXmlAttributeValue(labNode, "low", "value");
                string resultValue = XmlUtils.getXmlAttributeValue(labNode, "result", "value");
                string resultDate = XmlUtils.getXmlAttributeValue(labNode, "resulted", "value");
                string sample = XmlUtils.getXmlAttributeValue(labNode, "sample", "value");
                string specimenCode = XmlUtils.getXmlAttributeValue(labNode, "specimen", "code");
                string specimenType = XmlUtils.getXmlAttributeValue(labNode, "specimen", "name");
                string status = XmlUtils.getXmlAttributeValue(labNode, "status", "value");
                string testName = XmlUtils.getXmlAttributeValue(labNode, "test", "value");
                string testDataType = XmlUtils.getXmlAttributeValue(labNode, "type", "value"); // CH, MI, CY, EM, SP, AU, BB
                string testUnits = XmlUtils.getXmlAttributeValue(labNode, "units", "value");
                string vuid = XmlUtils.getXmlAttributeValue(labNode, "vuid", "value");

                string labOrderId = XmlUtils.getXmlAttributeValue(labNode, "labOrderID", "value");
                string orderId = XmlUtils.getXmlAttributeValue(labNode, "orderID", "value");

                //TestType lab = new CCRHelper().buildLabObject(testId, accessionNumber, testDataType, specimenType, status, collected, resultDate, resultValue, testUnits, testLoinc, "LOINC", testLowRef, testHighRef);
                
                LabSpecimen specimen = new LabSpecimen(specimenCode, specimenType, collected, accessionNumber);
                LabTest labTest = new LabTest();
                LabReport labReport = new LabReport();
                labReport.Panel = new LabPanel();

                labTest.Specimen = specimen;
                labTest.DataName = testName;
                labTest.DataType = testDataType;
                labTest.HiRef = testHighRef;
                labTest.Loinc = testLoinc;
                labTest.LowRef = testLowRef;
                labTest.Name = testName;
                if (!String.IsNullOrEmpty(testLowRef) && !String.IsNullOrEmpty(testHighRef))
                {
                    labTest.RefRange = testLowRef + " - " + testHighRef;
                }
                labTest.ShortName = testShortName;
                labTest.Units = testUnits;
                
                labTest.Result = new LabResult();
                labTest.Result.BoundaryStatus = interpretation;
                labTest.Result.Comment = comment;
                labTest.Result.LabSiteId = facility.Key;
                labTest.Result.SpecimenType = specimenType;
                labTest.Result.Test = labTest;
                labTest.Result.Value = resultValue;
                labTest.Result.Timestamp = resultDate;

                labReport.Type = testDataType;
                labReport.Id = id;

                if (!results.ContainsKey(accessionNumber))
                {
                    labReport.Panel.Tests = new List<LabTest>();
                    results.Add(accessionNumber, labReport);
                }
                results[accessionNumber].Panel.Tests.Add(labTest);
            }

            // supplements
            // first organize by type since that's how our supplemental calls work
            Dictionary<string, IList<LabReport>> labsByType = new Dictionary<string, IList<LabReport>>();
            foreach (LabReport rpt in results.Values)
            {
                if (!labsByType.ContainsKey(rpt.Type))
                {
                    labsByType.Add(rpt.Type, new List<LabReport>());
                }
                labsByType[rpt.Type].Add(rpt);
            }
            // the return object labs - copy over from keys
            foreach (string key in results.Keys)
            {
                labs.Add(results[key]);
            }

            // end type organize
                // Chemistry/Hematology 
            if (labsByType.ContainsKey("CH"))
            {
                ChemHemReport[] rpts = new VistaChemHemDao(this.cxn).getChemHemReports("", "", 10000);
                if (rpts != null && rpts.Length > 0)
                {
                    for (int i = 0; i < rpts.Length; i++)
                    {
                        if (String.IsNullOrEmpty(rpts[i].Id))
                        {
                            continue;
                        }
                        for (int j = 0; j < labsByType["CH"].Count; j++)
                        {
                            if (String.IsNullOrEmpty(labsByType["CH"][j].Id))
                            {
                                continue;
                            }
                            // labsByType["CH"][j].id looks like: "CH;6929196.925275;507114"
                            // rpts[i].id looks like: "6929196.925275"
                            if (labsByType["CH"][j].Id.Contains(rpts[i].Id))
                            {
                                labsByType["CH"][j].Text = rpts[i].Comment; // the getChemHemReports supplemental call stores the report in the comment fiels
                                break;
                            }
                        }
                    }
                }
            }
            // end supplements

            return labs;
        }

        internal IList<Visit> toVisitsFromXmlNode(XmlNode node)
        {
            IList<Visit> visits = new List<Visit>();
            
            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return visits;
            }

            XmlNodeList visitNodes = node.SelectNodes("visit");
            if (visitNodes == null || visitNodes.Count == 0)
            {
                return visits;
            }

            foreach (XmlNode visitNode in visitNodes)
            {
                Visit visit = new Visit();
                visit.Timestamp = XmlUtils.getXmlAttributeValue(visitNode, "dateTime", "value");
                visit.Id = XmlUtils.getXmlAttributeValue(visitNode, "id", "value");
                visit.Location = new HospitalLocation() { Name = XmlUtils.getXmlAttributeValue(visitNode, "location", "value") };
                visit.PatientType = XmlUtils.getXmlAttributeValue(visitNode, "patientClass", "value");
                visit.Service = XmlUtils.getXmlAttributeValue(visitNode, "service", "value");
                visit.VisitId = XmlUtils.getXmlAttributeValue(visitNode, "visitString", "value");
                visit.Type = XmlUtils.getXmlAttributeValue(visitNode, "type", "name");

                visits.Add(visit);
            }


            return visits;
        }

        internal IList<PatientRecordFlag> toFlagsFromXmlNode(XmlNode node)
        {
            IList<PatientRecordFlag> flags = new List<PatientRecordFlag>();

            return flags;
        }

        internal IList<Consult> toConsultsFromXmlNode(XmlNode node)
        {
            IList<Consult> consults = new List<Consult>();

            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return consults;
            }

            XmlNodeList consultNodes = node.SelectNodes("consult");
            if (consultNodes == null || consultNodes.Count == 0)
            {
                return consults;
            }

            foreach (XmlNode consultNode in consultNodes)
            {
                Consult consult = new Consult();
                consult.Id = XmlUtils.getXmlAttributeValue(consultNode, "id", "value");
                consult.Title = XmlUtils.getXmlAttributeValue(consultNode, "name", "value");
                consult.Status = XmlUtils.getXmlAttributeValue(consultNode, "status", "value");
                consult.Type = new OrderType() { Id = XmlUtils.getXmlAttributeValue(consultNode, "orderID", "value"), Name1 = XmlUtils.getXmlAttributeValue(consultNode, "type", "value") };

                consults.Add(consult);
            }

            return consults;
        }

        internal IList<HealthSummary> toHealthSummariesFromXmlNode(XmlNode node)
        {
            IList<HealthSummary> summaries = new List<HealthSummary>();

            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return summaries;
            }

            XmlNodeList summaryNodes = node.SelectNodes("factor");
            if (summaryNodes == null || summaryNodes.Count == 0)
            {
                return summaries;
            }

            foreach (XmlNode summaryNode in summaryNodes)
            {
                HealthSummary summary = new HealthSummary();
                summary.Id = XmlUtils.getXmlAttributeValue(summaryNode, "id", "value");
                summary.Text = XmlUtils.getXmlAttributeValue(summaryNode, "comment", "value");
                summary.Title = XmlUtils.getXmlAttributeValue(summaryNode, "name", "value");

                summaries.Add(summary);
            }

            return summaries;
        }

        internal IList<Note> toNotesFromXmlNode(XmlNode node)
        {
            IList<Note> notes = new List<Note>();

            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return notes;
            }

            XmlNodeList noteNodes = node.SelectNodes("document");
            if (noteNodes == null || noteNodes.Count == 0)
            {
                return notes;
            }

            foreach (XmlNode noteNode in noteNodes)
            {
                Note note = new Note();

                XmlNode textNode = noteNode.SelectSingleNode("content");
                string noteText = "";
                if (textNode != null)
                {
                    noteText = textNode.InnerXml;
                }

                string category = XmlUtils.getXmlAttributeValue(noteNode, "category", "value");
                string documentClass = XmlUtils.getXmlAttributeValue(noteNode, "documentClass", "value");
                string encounter = XmlUtils.getXmlAttributeValue(noteNode, "encounter", "value");
                string facilityId = XmlUtils.getXmlAttributeValue(noteNode, "facility", "code");
                string facilityName = XmlUtils.getXmlAttributeValue(noteNode, "facility", "name");
                string noteId = XmlUtils.getXmlAttributeValue(noteNode, "id", "value");
                string localTitle = XmlUtils.getXmlAttributeValue(noteNode, "localTitle", "value");
                string nationalTitleId = XmlUtils.getXmlAttributeValue(noteNode, "nationalTitle", "code");
                string nationalTitleName = XmlUtils.getXmlAttributeValue(noteNode, "nationalTitle", "name");
                string nationalTitleTypeId = XmlUtils.getXmlAttributeValue(noteNode, "nationalTitleType", "code");
                string nationalTitleTypeName = XmlUtils.getXmlAttributeValue(noteNode, "nationalTitleType", "name");
                string timestamp = XmlUtils.getXmlAttributeValue(noteNode, "referenceDateTime", "value");
                string status = XmlUtils.getXmlAttributeValue(noteNode, "status", "value");
                string type = XmlUtils.getXmlAttributeValue(noteNode, "type", "value");

                XmlNodeList authorNodes = noteNode.SelectNodes("clinicians/clinician");
                if (authorNodes != null && authorNodes.Count > 0)
                {
                    foreach (XmlNode authorNode in authorNodes)
                    {
                        string authorRole = XmlUtils.getXmlAttributeValue(authorNode, "/", "role");
                        string authorId = XmlUtils.getXmlAttributeValue(authorNode, "/", "code");
                        string authorName = XmlUtils.getXmlAttributeValue(authorNode, "/", "name");
                        string signatureTimestamp = XmlUtils.getXmlAttributeValue(authorNode, "/", "dateTime");
                        string signature = XmlUtils.getXmlAttributeValue(authorNode, "/", "signature");

                        Author newAuthor = new Author()
                        {
                            Id = authorId,
                            Name = authorName,
                            Signature = signature
                        };

                        if (!String.IsNullOrEmpty(authorRole))
                        {
                            if (String.Equals(authorRole, "A"))
                            {
                                note.Author = newAuthor;
                            }
                            else if (String.Equals(authorRole, "S"))
                            {
                                note.ProcTimestamp = signatureTimestamp;
                                note.ApprovedBy = newAuthor;
                            }
                            else if (String.Equals(authorRole, "C"))
                            {
                                note.Cosigner = newAuthor;
                            }
                        }
                    }
                }

                note.Type = type;
                note.ConsultId = encounter;
                note.SiteId = new SiteId(facilityId, facilityName);
                note.Id = noteId;
                note.LocalTitle = localTitle;
                note.StandardTitle = nationalTitleName;
                note.Timestamp = timestamp;
                note.Status = status;
                note.Text = noteText;

                notes.Add(note);
            }

            // NOTE - NO LONGER NEEDED!!! Figured out how to get text with VPR results!
            // supplement note text 
            //VistaNoteDao noteDao = new VistaNoteDao(this.cxn);
            //Note[] allNotes = noteDao.getNotes("", "", 10000);
            //if (allNotes != null && allNotes.Length > 0)
            //{
            //    for (int i = 0; i < allNotes.Length; i++)
            //    {
            //        for (int j = 0; j < notes.Count; j++)
            //        {
            //            if (String.Equals(allNotes[i].Id, notes[j].Id))
            //            {
            //                notes[j].Text = allNotes[i].Text;
            //                break;
            //            }
            //        }
            //    }
            //}
            // end supplement

            return notes;
        }

        internal IList<Appointment> toAppointmentsFromXmlNode(XmlNode node)
        {
            IList<Appointment> appointments = new List<Appointment>();

            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return appointments;
            }

            XmlNodeList appointmentNodes = node.SelectNodes("appointment");
            if (appointmentNodes == null || appointmentNodes.Count == 0)
            {
                return appointments;
            }

            foreach (XmlNode appointmentNode in appointmentNodes)
            {
                Appointment appointment = new Appointment();
                appointment.Clinic = new HospitalLocation(XmlUtils.getXmlAttributeValue(appointmentNode, "clinicStop", "code"), XmlUtils.getXmlAttributeValue(appointmentNode, "clinicStop", "name"));
                appointment.Facility = new SiteId(XmlUtils.getXmlAttributeValue(appointmentNode, "facility", "code"), XmlUtils.getXmlAttributeValue(appointmentNode, "facility", "name"));
                appointment.Id = XmlUtils.getXmlAttributeValue(appointmentNode, "id", "value");
                appointment.Status = XmlUtils.getXmlAttributeValue(appointmentNode, "apptStatus", "value");
                appointment.Timestamp = XmlUtils.getXmlAttributeValue(appointmentNode, "dateTime", "value");
                appointment.Type = XmlUtils.getXmlAttributeValue(appointmentNode, "type", "name");

                appointments.Add(appointment);
            }
            return appointments;
        }

        internal object toMedsFromXmlNode(XmlNode node, Type returnType)
        {
            if (!(returnType == typeof(IList<Medication>)) && !(returnType == typeof(Medications)))
            {
                throw new MdoException("Currently able to return MDO Medication list or CCR Medications only");
            }

            object result = null;
            if (returnType == typeof(IList<Medication>))
            {
                result = new List<Medication>();
            }
            else if (returnType == typeof(Medications))
            {
                result = new Medications();
                ((Medications)result).Medication = new List<StructuredProductType>();
            }

            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return result;
            }
            XmlNodeList medNodes = node.SelectNodes("med");
            if (medNodes == null || medNodes.Count == 0)
            {
                return result;
            }

            CCRHelper ccrHelper = new CCRHelper();

            foreach (XmlNode medNode in medNodes)
            {
                string cost = XmlUtils.getXmlAttributeValue(medNode, "fillCost", "value");
                string daysSupply = XmlUtils.getXmlAttributeValue(medNode, "daysSupply", "value");
                SiteId facility = new SiteId(XmlUtils.getXmlAttributeValue(medNode, "facility", "code"), XmlUtils.getXmlAttributeValue(medNode, "facility", "name"));
                string medId = XmlUtils.getXmlAttributeValue(medNode, "medID", "value");
                KeyValuePair<string, string> hospitalLocation = 
                    new KeyValuePair<string, string>(XmlUtils.getXmlAttributeValue(medNode, "location", "code"), XmlUtils.getXmlAttributeValue(medNode, "location", "name"));
                string medName = XmlUtils.getXmlAttributeValue(medNode, "name", "value");

                string orderId = XmlUtils.getXmlAttributeValue(medNode, "orderID", "value");
                string pharmId = XmlUtils.getXmlAttributeValue(medNode, "id", "value");
                
                bool isOutpatient = false, isInpatient = false, isUnitDose = false, isNonVA = false, isIV = false;
                if (!String.IsNullOrEmpty(medId) && medId.Contains(";")) // check outpatient by this ID - e.g. 40494;O
                {
                    string[] pieces = medId.Split(new char[1] { ';' });
                    if (pieces != null && pieces.Length == 2 && !String.IsNullOrEmpty(pieces[1]))
                    {
                        isOutpatient = String.Equals(pieces[1], "O");
                        isInpatient = String.Equals(pieces[1], "I");
                        isUnitDose = pieces[0].EndsWith("U");
                        isNonVA = pieces[0].EndsWith("N");
                        isIV = pieces[0].EndsWith("V");
                        // found a "P" code - what's that??? e.g.: 771P;I
                    }
                }
                Author provider = new Author(XmlUtils.getXmlAttributeValue(medNode, "orderingProvider", "code"), XmlUtils.getXmlAttributeValue(medNode, "orderingProvider", "name"), "");
                string quantity = XmlUtils.getXmlAttributeValue(medNode, "quantity", "value");
                string refills = XmlUtils.getXmlAttributeValue(medNode, "fillsAllowed", "value");
                string refillsRemaining = XmlUtils.getXmlAttributeValue(medNode, "fillsRemaining", "value");
                string rxNumber = XmlUtils.getXmlAttributeValue(medNode, "prescription", "value");

                string sig = "";
                XmlNode sigNode = medNode.SelectSingleNode("sig");
                if (sigNode != null)
                {
                    sig = sigNode.InnerXml;
                }
                string status = XmlUtils.getXmlAttributeValue(medNode, "vaStatus", "value");
                string medType = XmlUtils.getXmlAttributeValue(medNode, "vaType", "value");
                string dose = XmlUtils.getXmlAttributeValue(medNode, "doses/dose", "dose");
                string route = XmlUtils.getXmlAttributeValue(medNode, "doses/dose", "route");
                string schedule = XmlUtils.getXmlAttributeValue(medNode, "doses/dose", "schedule");
                string rate = XmlUtils.getXmlAttributeValue(medNode, "rate", "value");

                // this should be a dictionary - can have more than 1 orderable item associate with a med (e.g. two components of a solution for IV med)
                // these should be the <products> nodes
                //med.PharmacyOrderableItem 

                string expires = XmlUtils.getXmlAttributeValue(medNode, "expires", "value");
                string issued = XmlUtils.getXmlAttributeValue(medNode, "ordered", "value");
                string lastFilled = XmlUtils.getXmlAttributeValue(medNode, "lastFilled", "value");
                string startDate = XmlUtils.getXmlAttributeValue(medNode, "start", "value");
                string stopDate = XmlUtils.getXmlAttributeValue(medNode, "stop", "value");

                // extra data in CCR med only
                string unitsPerDose = XmlUtils.getXmlAttributeValue(medNode, "doses/dose", "unitsPerDose");
                string units = XmlUtils.getXmlAttributeValue(medNode, "doses/dose", "units");
                string form = XmlUtils.getXmlAttributeValue(medNode, "doses/dose", "noun");

                if (returnType == typeof(IList<Medication>))
                {
                    Medication med = new Medication();
                    med.Cost = cost;
                    med.DaysSupply = daysSupply;
                    med.Dose = dose;
                    med.Facility = facility;
                    med.Hospital = hospitalLocation;
                    med.Id = medId;
                    med.IsInpatient = isInpatient;
                    med.IsIV = isIV;
                    med.IsNonVA = isNonVA;
                    med.IsOutpatient = isOutpatient;
                    med.IsUnitDose = isUnitDose;
                    med.Name = medName;
                    med.OrderId = orderId;
                    med.PharmacyId = pharmId;
                    med.Provider = provider;
                    med.Quantity = quantity;
                    med.Rate = rate;
                    med.Refills = refills;
                    med.Remaining = refillsRemaining;
                    med.Route = route;
                    med.RxNumber = rxNumber;
                    med.Schedule = schedule;
                    med.Sig = sig;
                    med.Status = status;
                    med.Type = medType;

                    med.ExpirationDate = expires;
                    med.IssueDate = issued;
                    med.LastFillDate = lastFilled;
                    med.StartDate = startDate;
                    med.StopDate = stopDate;

                    ((IList<Medication>)result).Add(med);
                }
                else if (returnType == typeof(Medications))
                {
                    StructuredProductType ccrMed = ccrHelper.buildMedObject(medName, medId, pharmId, orderId, rxNumber, 
                        startDate, stopDate, issued, lastFilled, expires,
                        sig, dose, units, form, unitsPerDose, schedule, route, refills, refillsRemaining, quantity,
                        provider.Name, provider.Id, status, medType);

                    ((Medications)result).Medication.Add(ccrMed);
                }
            }

            return result;
        }


        internal IList<VitalSignSet> toVitalsFromXmlNode(XmlNode node)
        {
            IList<VitalSignSet> vitals = new List<VitalSignSet>();

            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return vitals;
            }

            XmlNodeList vitalsNodes = node.SelectNodes("vital");
            if (vitalsNodes == null || vitalsNodes.Count == 0)
            {
                return vitals;
            }

            foreach (XmlNode vitalNode in vitalsNodes)
            {
                VitalSignSet vitalSignSet = new VitalSignSet();

                string entered = XmlUtils.getXmlAttributeValue(vitalNode, "entered", "value");
                KeyValuePair<string, string> facility = new KeyValuePair<string, string>
                    (XmlUtils.getXmlAttributeValue(vitalNode, "facility", "code"), XmlUtils.getXmlAttributeValue(vitalNode, "facility", "name"));
                KeyValuePair<string, string> location = new KeyValuePair<string, string>
                    (XmlUtils.getXmlAttributeValue(vitalNode, "location", "code"), XmlUtils.getXmlAttributeValue(vitalNode, "location", "name"));
                string taken = XmlUtils.getXmlAttributeValue(vitalNode, "taken", "value");

                XmlNodeList measurementNodes = vitalNode.SelectNodes("measurements/measurement");
                if (measurementNodes == null || measurementNodes.Count == 0)
                {
                    continue;
                }

                vitalSignSet.Entered = entered;
                vitalSignSet.Timestamp = taken;
                vitalSignSet.Facility = new SiteId(facility.Key, facility.Value);
                vitalSignSet.Location = new HospitalLocation(location.Key, location.Value);

                foreach (XmlNode measurementNode in measurementNodes)
                {
                    VitalSign vital = new VitalSign();
                    vital.Location = new HospitalLocation(location.Key, location.Value);

                    string measurementId = XmlUtils.getXmlAttributeValue(measurementNode, "/", "id");
                    string vuid = XmlUtils.getXmlAttributeValue(measurementNode, "/", "vuid");
                    string name = XmlUtils.getXmlAttributeValue(measurementNode, "/", "name");
                    string value = XmlUtils.getXmlAttributeValue(measurementNode, "/", "value");
                    string metricValue = XmlUtils.getXmlAttributeValue(measurementNode, "/", "metricValue");
                    string units = XmlUtils.getXmlAttributeValue(measurementNode, "/", "units");
                    string metricUnits = XmlUtils.getXmlAttributeValue(measurementNode, "/", "metricUnits");
                    string high = XmlUtils.getXmlAttributeValue(measurementNode, "/", "high");
                    string low = XmlUtils.getXmlAttributeValue(measurementNode, "/", "low");

                    vital.Id = measurementId;
                    vital.Type = new ObservationType(measurementId, vuid, name);
                    vital.Units = units;
                    vital.Value1 = value;

                    XmlNodeList qualifiersNodes = measurementNode.SelectNodes("qualifiers/qualifier");
                    if (qualifiersNodes != null || qualifiersNodes.Count > 0)
                    {
                        foreach (XmlNode qualifiersNode in qualifiersNodes)
                        {
                            string qualifierName = XmlUtils.getXmlAttributeValue(qualifiersNode, "/", "name");
                            string qualifierId = XmlUtils.getXmlAttributeValue(qualifiersNode, "/", "vuid");

                            vital.Qualifiers = qualifierName;
                        }
                    }

                    vitalSignSet.addVitalSign(name, vital);
                }

                vitals.Add(vitalSignSet);
            }

            return vitals;
        }

        internal IList<Problem> toProblemsFromXmlNode(XmlNode node)
        {
            IList<Problem> problems = new List<Problem>();

            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return problems;
            }

            XmlNodeList problemNodes = node.SelectNodes("problem");
            if (problemNodes == null || problemNodes.Count == 0)
            {
                return problems;
            }

            foreach (XmlNode problemNode in problemNodes)
            {
                string acuityCode = XmlUtils.getXmlAttributeValue(problemNode, "acuity", "code");
                string acuityName = XmlUtils.getXmlAttributeValue(problemNode, "acuity", "name");
                string code = XmlUtils.getXmlAttributeValue(problemNode, "code", "value");
                string entered = XmlUtils.getXmlAttributeValue(problemNode, "entered", "value");
                SiteId facility = new SiteId(XmlUtils.getXmlAttributeValue(problemNode, "facility", "code"), XmlUtils.getXmlAttributeValue(problemNode, "facility", "name"));
                string historyCode = XmlUtils.getXmlAttributeValue(problemNode, "history", "code");
                string historyValue = XmlUtils.getXmlAttributeValue(problemNode, "history", "name");
                string icd = XmlUtils.getXmlAttributeValue(problemNode, "icd", "value");
                string id = XmlUtils.getXmlAttributeValue(problemNode, "id", "value");
                bool isServiceConnected = (XmlUtils.getXmlAttributeValue(problemNode, "sc", "value") == "1");
                HospitalLocation location = new HospitalLocation(XmlUtils.getXmlAttributeValue(problemNode, "location", "code"), XmlUtils.getXmlAttributeValue(problemNode, "location", "value"));
                string modifiedDate = XmlUtils.getXmlAttributeValue(problemNode, "updated", "value");
                Author observer = new Author(XmlUtils.getXmlAttributeValue(problemNode, "provider", "code"), XmlUtils.getXmlAttributeValue(problemNode, "provider", "name"), "");
                string onsetDate = XmlUtils.getXmlAttributeValue(problemNode, "onset", "value");
                bool removed = (XmlUtils.getXmlAttributeValue(problemNode, "removed", "value") == "1");
                string resolvedDate = XmlUtils.getXmlAttributeValue(problemNode, "resolved", "value");
                string statusCode = XmlUtils.getXmlAttributeValue(problemNode, "status", "code");
                string statusName = XmlUtils.getXmlAttributeValue(problemNode, "status", "name");
                bool verified = (XmlUtils.getXmlAttributeValue(problemNode, "unverified", "value") == "0");
                string name = XmlUtils.getXmlAttributeValue(problemNode, "name", "value");
                string type = XmlUtils.getXmlAttributeValue(problemNode, "problemType", "value");
                
                IList<Note> comments = new List<Note>();
                XmlNodeList commentNodes = problemNode.SelectNodes("comments/comment");
                if (commentNodes != null && commentNodes.Count > 0)
                {
                    foreach (XmlNode commentNode in commentNodes)
                    {
                        Note newNote = new Note();

                        string commentId = XmlUtils.getXmlAttributeValue(commentNode, "/", "id");
                        string commentTimestamp = XmlUtils.getXmlAttributeValue(commentNode, "/", "entered");
                        string commentAuthor = XmlUtils.getXmlAttributeValue(commentNode, "/", "enteredBy");
                        string commentText = XmlUtils.getXmlAttributeValue(commentNode, "/", "commentText");

                        newNote.Id = commentId;
                        newNote.Timestamp = commentTimestamp;
                        newNote.Author = new Author() { Name = commentAuthor };
                        newNote.Text = commentText;

                        comments.Add(newNote);
                    }
                }

                Problem problem = new Problem();

                problem.Acuity = new KeyValuePair<string, string>(acuityCode, acuityName);
                problem.Comments = comments;
                problem.Timestamp = entered;
                problem.Facility = facility;
                problem.Icd = icd;
                problem.Id = id;
                problem.IsServiceConnected = isServiceConnected;
                problem.Location = location;
                problem.ModifiedDate = modifiedDate;
                problem.Observer = observer;
                problem.OnsetDate = onsetDate;
                problem.Removed = removed;
                problem.ResolvedDate = resolvedDate;
                problem.Status = statusName;
                problem.Type = new ObservationType(type, "", name);
                if (!String.IsNullOrEmpty(type))
                {
                    if (String.Equals(type, "55607006"))
                    {
                        problem.Type.Category = "Problem";
                    }
                    else if (String.Equals(type, "64572001"))
                    {
                        problem.Type.Category = "Condition";
                    }
                }
                problem.Verified = verified;

                problems.Add(problem);
            }

            return problems;
        }

        internal IList<Allergy> toAllergiesFromXmlNode(XmlNode node)
        {
            IList<Allergy> allergies = new List<Allergy>();

            int total = verifyTopLevelNode(node);
            if (total == 0)
            {
                return allergies;
            }

            XmlNodeList allergyNodes = node.SelectNodes("allergy");
            if (allergyNodes == null || allergyNodes.Count == 0)
            {
                return allergies;
            }

            foreach (XmlNode allergyNode in allergyNodes)
            {
                Allergy allergy = new Allergy();

                XmlNodeList drugClassesNodes = allergyNode.SelectNodes("drugClasses/drugClass");
                if (drugClassesNodes != null && drugClassesNodes.Count > 0)
                {
                    allergy.DrugClasses = new StringDictionary();
                    foreach (XmlNode drugClassNode in drugClassesNodes)
                    {
                        string vuid = XmlUtils.getXmlAttributeValue(drugClassNode, "/", "vuid");
                        string name = XmlUtils.getXmlAttributeValue(drugClassNode, "/", "name");
                        if (!String.IsNullOrEmpty(vuid))
                        {
                            allergy.DrugClasses.Add(vuid, name);
                        }
                    }
                }

                XmlNodeList drugIngredientsNodes = allergyNode.SelectNodes("drugIngredients/drugIngredient");
                if (drugIngredientsNodes != null && drugIngredientsNodes.Count > 0)
                {
                    allergy.DrugIngredients = new StringDictionary();
                    foreach (XmlNode drugIngredientNode in drugIngredientsNodes)
                    {
                        string vuid = XmlUtils.getXmlAttributeValue(drugIngredientNode, "/", "vuid");
                        string name = XmlUtils.getXmlAttributeValue(drugIngredientNode, "/", "name");
                        if (!String.IsNullOrEmpty(vuid))
                        {
                            allergy.DrugIngredients.Add(vuid, name);
                        }
                    }
                }

                XmlNodeList reactionsNodes = allergyNode.SelectNodes("reactions/reaction");
                if (reactionsNodes != null && reactionsNodes.Count > 0)
                {
                    allergy.Reactions = new List<Symptom>();
                    foreach (XmlNode reactionNode in reactionsNodes)
                    {
                        Symptom symptom = new Symptom();
                        symptom.Name = XmlUtils.getXmlAttributeValue(reactionNode, "/", "name");
                        symptom.Id = XmlUtils.getXmlAttributeValue(reactionNode, "/", "vuid");
                        allergy.Reactions.Add(symptom);
                    }
                }

                allergy.Timestamp = XmlUtils.getXmlAttributeValue(allergyNode, "entered", "value");
                allergy.AllergenId = XmlUtils.getXmlAttributeValue(allergyNode, "id", "value");
                allergy.AllergenName = XmlUtils.getXmlAttributeValue(allergyNode, "name", "value");
                allergy.AllergenType = XmlUtils.getXmlAttributeValue(allergyNode, "type", "name");
                allergy.Comment = XmlUtils.getXmlAttributeValue(allergyNode, "assessment", "value");
                allergy.Facility = new SiteId(XmlUtils.getXmlAttributeValue(allergyNode, "facility", "code"), XmlUtils.getXmlAttributeValue(allergyNode, "facility", "name"));

                string observationType = XmlUtils.getXmlAttributeValue(allergyNode, "source", "value");
                if (!String.IsNullOrEmpty(observationType))
                {
                    allergy.Type = new ObservationType(observationType, "Allergies and Adverse Reactions", 
                        String.Equals(observationType, "H", StringComparison.CurrentCultureIgnoreCase) ? "Historical" : "Observed");
                }

                allergies.Add(allergy);
            }

            return allergies;
        }

        internal Patient toPatientFromXmlNode(XmlNode node)
        {
            Patient patient = new Patient();

            if (node == null || node.Attributes == null || node.Attributes.Count == 0 || node.Attributes["total"] == null)
            {
                return patient;
            }
            string strTotal = node.Attributes["total"].Value;
            int total = 0;
            if (!Int32.TryParse(strTotal, out total))
            {
                return patient;
            }

            XmlNodeList patientNodes = node.SelectNodes("patient");
            if (patientNodes == null || patientNodes.Count == 0)
            {
                return patient;
            }

            node = patientNodes[0];

            try
            {
                patient.DOB = XmlUtils.getXmlAttributeValue(node, "dob", "value");
                patient.LocalPid = XmlUtils.getXmlAttributeValue(node, "id", "value");
                patient.IsServiceConnected = (XmlUtils.getXmlAttributeValue(node, "sc", "value") == "1");
                if (patient.IsServiceConnected)
                {
                    Int32 scPercent = 0;
                    if (Int32.TryParse(XmlUtils.getXmlAttributeValue(node, "scPercent", "value"), out scPercent))
                    {
                        patient.ScPercent = scPercent;
                    }
                }
                patient.SSN = new SocSecNum(XmlUtils.getXmlAttributeValue(node, "ssn", "value"));
                patient.MpiPid = XmlUtils.getXmlAttributeValue(node, "icn", "value");
                patient.MaritalStatus = XmlUtils.getXmlAttributeValue(node, "maritalStatus", "value");
                patient.Ethnicity = XmlUtils.getXmlAttributeValue(node, "ethnicities/ethnicity", "value");
                patient.Name = new PersonName(XmlUtils.getXmlAttributeValue(node, "fullName", "value"));
                patient.Gender = XmlUtils.getXmlAttributeValue(node, "gender", "value");

                patient.SitePids = new StringDictionary();
                XmlNode facilitiesNode = node.SelectSingleNode("facilities");
                if (facilitiesNode != null && facilitiesNode.ChildNodes != null && facilitiesNode.ChildNodes.Count > 0)
                {
                    foreach (XmlNode childNode in facilitiesNode.ChildNodes)
                    {
                        string sitecode = childNode.Attributes["code"].Value;
                        string siteName = childNode.Attributes["name"].Value;
                        if (!patient.SitePids.ContainsKey(sitecode))
                        {
                            patient.SitePids.Add(sitecode, siteName);
                        }
                        if (childNode.Attributes["homeSite"] != null || facilitiesNode.ChildNodes.Count == 1)
                        {
                            patient.CmorSiteId = sitecode;
                        }
                    }
                }
                patient.Demographics = new Dictionary<string, DemographicSet>();
                patient.Demographics.Add(cxn.DataSource.SiteId.Id, new DemographicSet());
                Address address = new Address();
                address.Street1 = XmlUtils.getXmlAttributeValue(node, "address", "streetLine1");
                address.Street2 = XmlUtils.getXmlAttributeValue(node, "address", "streetLine2");
                address.Street3 = XmlUtils.getXmlAttributeValue(node, "address", "streetLine3");
                address.City = XmlUtils.getXmlAttributeValue(node, "address", "city");
                address.State = XmlUtils.getXmlAttributeValue(node, "address", "stateProvince");
                address.Zipcode = XmlUtils.getXmlAttributeValue(node, "address", "postalCode");
                patient.Demographics[cxn.DataSource.SiteId.Id].StreetAddresses = new List<Address>();
                patient.Demographics[cxn.DataSource.SiteId.Id].StreetAddresses.Add(address);

                patient.IsRestricted = (XmlUtils.getXmlAttributeValue(node, "sensitive", "value") == "1");
            }
            catch (Exception)
            {
                // how to handle... allow missing data?
            }
            return patient;
        }

        // end VPR XML PARSING
        #endregion

        #endregion

        #region Mental Health

        public List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatient()
        {
            return getMentalHealthInstrumentsForPatient(cxn.Pid);
        }

        

        public List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatient(string dfn)
        {
            DdrLister query = buildGetMentalHealthInstrumentsForPatientQuery(dfn);
            string[] response = query.execute();
            return toMentalHealthInstrumentAdministrations(response);
        }

        public List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatientBySurvey(string surveyName)
        {
            throw new NotImplementedException();
        }

        internal DdrLister buildGetMentalHealthInstrumentsForPatientQuery(string dfn)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = VistaConstants.MH_ADMINISTRATIONS;
            query.Fields = "1;1E;2;3;4;5;5E;6;6E;7;8;9;11;12;13";
            query.Xref = "C";
            query.Flags = "IP";
            query.From = VistaUtils.adjustForNumericSearch(dfn);
            query.Part = dfn;

            // This takes care of the hospital location name.
            query.Id = "S X=$P(^(0),U,11) I X'=\"\",$D(^SC(X,0)) S X=$P($G(^SC(X,0)),U) D EN^DDIOL(X)";
            return query;
        }

        internal List<MentalHealthInstrumentAdministration> toMentalHealthInstrumentAdministrations(string[] response)
        {
            if (response == null || response.Length == 0)
            {
                return null;
            }

            StringDictionary instruments = cxn.SystemFileHandler.getLookupTable(VistaConstants.MH_TESTS_AND_SURVEYS);

            List<MentalHealthInstrumentAdministration> result = new List<MentalHealthInstrumentAdministration>(response.Length);
            for (int i = 0; i < response.Length; i++)
            {
                MentalHealthInstrumentAdministration mhia = toMentalHealthInstrumentAdministration(response[i], instruments);
                if (mhia != null)
                {
                    result.Add(mhia);
                }
            }
            return result;
        }

        internal MentalHealthInstrumentAdministration toMentalHealthInstrumentAdministration(string response, StringDictionary instruments)
        {
            if (String.IsNullOrEmpty(response))
            {
                return null;
            }
            MentalHealthInstrumentAdministration result = new MentalHealthInstrumentAdministration();
            string[] flds = response.Split(new char[] { '^' });
            result.Id = flds[0];
            result.Patient = new KeyValuePair<string, string>(flds[1], flds[2]);
            string instrumentName = "";
            if (instruments.ContainsKey(flds[3]))
            {
                instrumentName = instruments[flds[3]];
            }
            result.Instrument = new KeyValuePair<string, string>(flds[3], instrumentName);
            result.DateAdministered = VistaTimestamp.toUtcString(flds[4]);
            result.DateSaved = VistaTimestamp.toUtcString(flds[5]);
            result.OrderedBy = new KeyValuePair<string, string>(flds[6], flds[7]);
            result.AdministeredBy = new KeyValuePair<string, string>(flds[8], flds[9]);
            result.IsSigned = flds[10] == "Y";
            result.IsComplete = flds[11] == "Y";
            result.NumberOfQuestionsAnswered = flds[12];
            result.TransmissionStatus = decodeMentalHealthInstrumentTransimissionStatus(flds[13]);
            result.TransmissionTime = VistaTimestamp.toUtcString(flds[14]);
            result.HospitalLocation = new KeyValuePair<string, string>(flds[15], flds[16]);
            return result;
        }

        internal string decodeMentalHealthInstrumentTransimissionStatus(string code)
        {
            if (code == "S")
            {
                return "Successfully added to db";
            }
            if (code == "T")
            {
                return "Transmitted, not yet added";
            }
            if (code == "E")
            {
                return "Error";
            }
            return "Invalid code: " + code;
        }

        public MentalHealthInstrumentResultSet getMentalHealthInstrumentResultSet(string administrationId)
        {
            DdrLister query = buildGetMentalHealthInstrumentResultSetQuery(administrationId);
            string[] response = query.execute();
            return toMentalHealthAdministrationResultSet(response);
        }

        public void addMentalHealthInstrumentResultSet(MentalHealthInstrumentAdministration administration)
        {
            DdrLister query = buildGetMentalHealthInstrumentResultSetQuery(administration.Id);
            string[] response = query.execute();
            administration.ResultSet = toMentalHealthAdministrationResultSet(response);
            administration.ResultSet.Instrument = administration.Instrument;
            administration.ResultSet.AdministrationId = administration.Id;
        }

        internal DdrLister buildGetMentalHealthInstrumentResultSetQuery(string ien)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = VistaConstants.MH_RESULTS;
            query.Fields = "2;3;4;5;6";
            query.Flags = "IP";
            query.Xref = "AC";
            query.From = VistaUtils.adjustForNumericSearch(ien);
            query.Part = ien;
            return query;
        }

        internal MentalHealthInstrumentResultSet toMentalHealthAdministrationResultSet(string[] response)
        {
            if (response == null || response.Length == 0)
            {
                return null;
            }
            MentalHealthInstrumentResultSet result = new MentalHealthInstrumentResultSet();
            string[] flds = response[0].Split(new char[] { '^' });
            result.Id = flds[0];

            // Note that we are getting the name of the Scale, not the pointer as the VistA
            // documentation claims.
            result.Scale = new KeyValuePair<string, string>("", flds[1]);

            result.RawScore = flds[2];
            result.TransformedScores.Add("1",flds[3]);
            result.TransformedScores.Add("2", flds[4]);
            result.TransformedScores.Add("3", flds[5]);
            return result;
        }

        public List<MentalHealthInstrumentResultSet> getMentalHealthInstrumentResultSetsBySurvey(string surveyName)
        {
            throw new NotImplementedException();
        }

        // Changed my mind. There are a whole bunch of fields in this and the immediate need is just for
        // the IEN and name so we'll do the StringD
        //public List<MentalHealthInstrument> getMentalHealthInstruments()
        //{
        //    DdrLister query = buildGetMentalHealthInstrumentsQuery();
        //    string[] response = query.execute();
        //    return toMentalHealthInstruments(response);
        //}

        //internal DdrLister buildGetMentalHealthInstrumentsQuery()
        //{
        //    DdrLister query = new DdrLister(cxn);
        //    query.File = VistaConstants.MH_TESTS_AND_SURVEYS;
        //    query.Fields = ".01;1;1E;2";
        //    query.Xref = "#";
        //    query.Flags = "IP";
        //    return query;
        //}

        #endregion

     
        #region Clinic Directory

        public User[] getStaffByCriteria(string siteCode, string searchTerm, string firstName, string lastName, string type)
        {
            throw new NotImplementedException();
        }

        #endregion


        
    }
}

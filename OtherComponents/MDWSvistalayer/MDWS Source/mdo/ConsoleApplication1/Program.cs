using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.mdo;
using gov.va.medora.utils;
using gov.va.medora.mdo.dao;

namespace ConsoleApplication1
{
    class Program
    {
        static void Main(string[] args)
        {
            Int32 port = 9200;
            String host = "127.0.0.1";
            if (args != null && args.Length > 1)
            {
                host = args[0];
                port = Convert.ToInt32(args[1]);
            }

            String accessCode = "01vehu";
            String verifyCode = "vehu01";
            if (args.Length > 3)
            {
                accessCode = args[2];
                verifyCode = args[3];
            }

            System.Console.WriteLine(String.Format("Connecting to {0}:{1} using credentials {2}/{3}...", host, port.ToString(), accessCode, verifyCode));
            VistaConnection cxn = login(host, port, accessCode, verifyCode);

            System.Console.WriteLine("Searching for a specific CPT code...");
            String cptIen = getCpt70551Ien(cxn);
            if (String.IsNullOrEmpty(cptIen))
            {
                System.Console.WriteLine("Preparing to create a new CPT record to use...");
                cptIen = sendCachedBuildDdrFilerCreateCptRecordString(cxn);
                System.Console.WriteLine("Created a new CPT record!! New record IEN: " + cptIen);
            }
            else
            {
                System.Console.WriteLine("Found CPT!! IEN: " + cptIen);
            }

            System.Console.WriteLine("Preparing to set CPT codes for radiology orderable items in file 71...");
            testEnableOrderableItemsForRadiology(cxn, cptIen);
            System.Console.WriteLine("Finished setting CPT codes!!");

            System.Console.WriteLine("Preparing to null CPT codes in file 101.43...");
            testUpdate101x43Item(cxn);
            System.Console.WriteLine("Finished nulling CPT codes!!");

            System.Console.WriteLine("Finishing up... Disconnecting from VistA...");
            cxn.disconnect();
            System.Console.WriteLine("Press enter key to exit...");
            System.Console.Read();
        }

        static String getCpt70551Ien(VistaConnection cxn)
        {
            String ienForRecord = new VistaToolsDao(cxn).getVariableValue("$O(^ICPT(\"B\",70551,\"\"))");
            return ienForRecord;
        }

        static VistaConnection login(String host, Int32 port, String accessCode, String verifyCode)
        {
            DataSource src = new DataSource() { SiteId = new SiteId() { Id = "500", Name = "Local" }, Provider = host, Modality = "HIS", Protocol = "VISTA", Port = port };
            VistaConnection cxn = new VistaConnection(src);
            cxn.ConnectStrategy = new VistaNatConnectStrategy(cxn);

            cxn.connect();

            AbstractCredentials credentials = new VistaCredentials();
            credentials.AccountName = accessCode;
            credentials.AccountPassword = verifyCode;
            cxn.Account.AuthenticationMethod = VistaConstants.LOGIN_CREDENTIALS; // TBD - VERY IMPORTANT!!!! SHOULD THIS BE Connection or Credentials?!?!?!?

            AbstractPermission permission = new MenuOption("DVBA CAPRI GUI");
            permission.IsPrimary = true;

            User authenticatedUser = cxn.Account.authenticateAndAuthorize(credentials, permission);
            System.Console.WriteLine("Successfully authenticated! Ready for some work...");

            return cxn;
        }


        static void printEntries(VistaConnection cxn)
        {
            Dictionary<String, String> rec1 = new VistaCrudDao(cxn).read("3134,", "*", "101.43", "IEN").Result as Dictionary<String, String>;
            // String[] rec1SubAmis = new VistaCrudDao(cxn).readRange("71.0135", ".01", ",537,", "IP", "#", "", "", "", "", "").Result as String[];
            // String[] rec1SubModality = new VistaCrudDao(cxn).readRange("71.0731", ".01", ",537,", "IP", "#", "", "", "", "", "").Result as String[];

            Dictionary<String, String> rec2 = new VistaCrudDao(cxn).read("3133,", "*", "101.43", "IEN").Result as Dictionary<String, String>;
            // String[] rec2SubAmis = new VistaCrudDao(cxn).readRange("71.0135", ".01", ",542,", "IP", "#", "", "", "", "", "").Result as String[];
            // String[] rec2SubModality = new VistaCrudDao(cxn).readRange("71.0731", ".01", ",542,", "IP", "#", "", "", "", "", "").Result as String[];

            IList<String> uniqueKeys = new List<String>();

            foreach (String key in rec1.Keys)
            {
                uniqueKeys.Add(key);
            }
            foreach (String key in rec2.Keys)
            {
                if (!uniqueKeys.Contains(key))
                {
                    uniqueKeys.Add(key);
                }
            }

            System.Console.WriteLine("\tRec 1 \t\t Rec 2");
            foreach (String key in uniqueKeys)
            {
                String rec1val = rec1.ContainsKey(key) ? rec1[key] : "<empty>";
                String rec2val = rec2.ContainsKey(key) ? rec2[key] : "<empty>";
                System.Console.WriteLine(String.Format("{0} : \t {1} \t\t {2}", key, rec1val, rec2val));
            }
        }

        static void testEnableOrderableItemsForRadiology(VistaConnection cxn, String cptIenToUse)
        {
            Dictionary<String, IList<String>> proceduresByType = getRadiologyOrderableItemsByType(cxn);

            foreach (String key in proceduresByType.Keys)
            {
                foreach (String procId in proceduresByType[key])
                {
                    setCpt(cxn, procId, key, cptIenToUse);
                    //break;
                }
                // break;
            }
        }

        static string sendCachedBuildDdrFilerCreateCptRecordString(AbstractConnection cxn)
        {
            String cachedRequestStr = "[XWB]113021.108\tDDR FILER50003ADDf2008\"IENs\",100570551t001102081^.01^+70551,^70551t001203081^2^+70551,^MRI BRAIN W/O DYEt001301681^3^+70551,^238t001401481^6^+70551,^Ct001502081^8^+70551,^2900101f";
            return toCreateUpdateDeleteRecordResponse((String)cxn.query(cachedRequestStr));
        }

        static String toCreateUpdateDeleteRecordResponse(string response)
        {
            String[] pieces = StringUtils.split(response, StringUtils.CRLF);

            if (pieces.Length > 1 && pieces[1].Contains("BEGIN_diERRORS"))
            {
                throw new Exception(response);
            }

            if (pieces[0].Contains("[Data]") && pieces.Length > 1) //sample create valid response: "[Data]\r\n+1,^2\r\n" <- 2 is IEN for new record
            {
                Int32 startIdx = pieces[1].IndexOf('^');
                return startIdx > 0 ? pieces[1].Substring(startIdx + 1) : "";
            }
            else // "[Data]" response means everything was ok
            {
                return "OK";
            }
        }

        static void setCpt(VistaConnection cxn, string procId, string procedureCode, String cptIenToUse)
        {
            Dictionary<String, String> cptsByCode = new Dictionary<string, string>();
            cptsByCode.Add("MR", "70551");
            cptsByCode.Add("CT", "74150");
            cptsByCode.Add("NM", "74150");
            cptsByCode.Add("US", "74150");
            cptsByCode.Add("MA", "70551");
            cptsByCode.Add("AG", "74150");
            cptsByCode.Add("OT", "74150");

            Dictionary<String, String> fieldsToUpdate = new Dictionary<string, string>();

            // fieldsToUpdate.Add("100", "3491231");
            fieldsToUpdate.Add("9", cptIenToUse);

            VistaCrudDao dao = new VistaCrudDao(cxn);
            CrudOperation updateResult = dao.update(fieldsToUpdate, (procId + ","), "71");

            //  Dictionary<String, String> fieldsForModality = new Dictionary<string, string>();
            //   fieldsForModality.Add(".01", "3");
            //  dao.create(fieldsForModality, "71.0731", String.Concat(procId, ","));
        }

        static Dictionary<String, IList<String>> getRadiologyOrderableItemsByType(VistaConnection cxn)
        {
            Dictionary<String, IList<String>> result = new Dictionary<string, IList<string>>();
            result.Add("MR", new List<String>()); // Magnetic Imaging
            result.Add("CT", new List<String>()); // Computed Tomagraphy
            result.Add("NM", new List<String>()); // Nuc Med
            result.Add("US", new List<String>()); // Ultrasound
            result.Add("MA", new List<String>()); // Mammography
            result.Add("AG", new List<String>()); // Angio
            result.Add("OT", new List<String>()); // Using for general radiology

            CrudOperation range = new VistaCrudDao(cxn).readRange("71", ".01;9;12", "", "IP", "#", "", "", "", "", "");
            String[] records = range.Result as String[];
            foreach (String record in records)
            {
                String[] pieces = StringUtils.split(record, StringUtils.CARET);
                if (pieces.Length < 4)
                {
                    continue;
                }
                String id = pieces[0];
                String cpt = pieces[2];
                String imagingType = pieces[3];
                if (String.IsNullOrEmpty(imagingType))
                {
                    continue;
                }
                switch (imagingType)
                {
                    case "1":
                        result["OT"].Add(id);
                        break;
                    case "2":
                        result["NM"].Add(id);
                        break;
                    case "3":
                        result["US"].Add(id);
                        break;
                    case "4":
                        result["MR"].Add(id);
                        break;
                    case "5":
                        result["CT"].Add(id);
                        break;
                    case "6":
                        result["AG"].Add(id);
                        break;
                    case "9":
                        result["MA"].Add(id);
                        break;
                    default:
                        break;
                }
            }

            return result;
        }

        //[Test]
        //public void testGetRadiologyOrderableItemsFrom101x43()
        static IList<String> getRadioOrderableItemsFrom101x43(VistaConnection cxn)
        {
            IList<String> result = new List<String>();

            String[] allOrderableItems = new VistaCrudDao(cxn).readRange("101.43", ".01;5", "", "IP", "#", "", "", "", "", "").Result as String[];
            foreach (String item in allOrderableItems)
            {
                String[] pieces = StringUtils.split(item, StringUtils.CARET);
                if (pieces.Length < 3)
                {
                    continue;
                }

                String ien = pieces[0];
                String name = pieces[1];
                String orderableItemType = pieces[2];

                if (String.Equals(orderableItemType, "34"))
                {
                    result.Add(ien);
                    //System.Console.WriteLine("Just added " + name + " to radiology orderable items from file 101.43");
                }
            }

            return result;
        }

        // RUN THIS TEST!!
        static void testUpdate101x43Item(VistaConnection cxn)
        {
            // old value for 101.43 record #3133, field 3 --> 72143 
            Dictionary<String, String> fieldsAndValues = new Dictionary<string, string>();
            fieldsAndValues.Add("3", "");

            IList<String> itemsToUpdate = getRadioOrderableItemsFrom101x43(cxn);

            foreach (String ien in itemsToUpdate)
            {
                new VistaCrudDao(cxn).update(fieldsAndValues, (ien + ","), "101.43");
                //System.Console.WriteLine("Just nulled CPT for record " + ien);
                // break;
            }
        }
    }
}

<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Administration.aspx.cs" Inherits="gov.va.medora.mdws.Web.Administration" %>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" >
<head runat="server">
    <title>MDWS Administration Console - All Your Bases Are Belong To Us!!!</title>
    <style type="text/css">
        body
        {
        	font-family: Sans-Serif;
        	margin: 0;
        	padding: 0;
        }
        .tableConfigHeader
        {
        	text-align: center;
        	font-size: larger;
        	font-family: Arial;
        	text-decoration: underline;
        }
        .tableConfigurationSpacer
        {
        	padding: 15px;
        }
        .tableConfigLabel
        {
        	font-weight: bold;
        	padding-left: 20px;
        }
        #pageContent
        {
        	margin-left: auto;
        	margin-right: auto;
        	width: 520px;
        }
        #pageForm
        {
        	margin-left: auto;
        	margin-right: auto;
        }
        #imageHeader
        {
        	margin-left: auto;
        	margin-right: auto;
        	width: 219px;
        }
        #tableDiv
        {
        	background-color: #247DB3;
        	color: White;
        	padding-right: 15px;
        	padding-left: 15px;
        	padding-top: 5px;
        	padding-bottom: 5px;
        	border-bottom: 2px solid black;
        	border-right: 2px solid black;
        	border-left: 1px solid gray;
        	border-top: 1px solid gray;
        }
        img
        {
            border: none;	
        }
        #versionLabel
        {
            text-align: right;
            color: Black;
            font-size: x-small;
        }
    </style>
</head>
<body><div id="container">
    
    <div id="pageContent">
    
    <div id="imageHeader"><img src="../images/mdws.gif" height="96" width="219" alt="" /></div>
    
    
    <form id="form1" runat="server">
    <div id="pageForm">
        <asp:Label ID="labelMessage" ForeColor="Red" Font-Bold="true" runat="server" />
        
        <asp:Panel ID="panelForm" runat="server">
        <div id="tableDiv">
        <table>
            <tbody>
                <tr>
                    <td colspan="3" class="tableConfigHeader">
                        MDWS Configuration<br />
                    </td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">Production Installation:</td>
                    <td>&nbsp;</td>
                    <td>
                        <asp:RadioButtonList ID="radioButtonMdwsProduction" runat="server">
                            <asp:ListItem Selected="True" Text="True" Value="true" />
                            <asp:ListItem Selected="False" Text="False" Value="false" />
                        </asp:RadioButtonList>
                    </td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">Log MDWS Sessions:</td>
                    <td>&nbsp;</td>
                    <td>
                        <asp:RadioButtonList ID="radioMdwsSessionsLogging" runat="server">
                            <asp:ListItem Selected="True" Text="True" Value="true" />
                            <asp:ListItem Selected="False" Text="False" Value="false" />
                        </asp:RadioButtonList>
                    </td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">MDWS Sessions Log Level:</td>
                    <td>&nbsp;</td>
                    <td>
                        <asp:RadioButtonList ID="radioMdwsSessionsLogLevel" runat="server">
                        </asp:RadioButtonList>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="3" class="tableConfigurationSpacer">&nbsp;</td>
                </tr>
                
                <tr>
                    <td colspan="3" class="tableConfigHeader">
                        Check a Vista connection<br />
                    </td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">IP Address:</td>
                    <td>&nbsp;</td>
                    <td><asp:TextBox ID="textboxVistaIp" Width="200" runat="server" /></td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">Port:</td>
                    <td>&nbsp;</td>
                    <td><asp:TextBox ID="textboxVistaPort" Width="200" MaxLength="5" runat="server" /></td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td><asp:Button ID="button1" Text="Test Vista Connectivity" OnClick="TestVistaSettingsClick" runat="server" /></td>
                </tr>
               
                <tr>
                    <td colspan="3" class="tableConfigurationSpacer">&nbsp;</td>
                </tr>
                
                <tr>
                    <td colspan="3" class="tableConfigHeader">
                        SQL Configuration<br />
                    </td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">SQL Server Path:</td>
                    <td>&nbsp;</td>
                    <td><asp:TextBox ID="textboxSqlServerPath" Width="200" runat="server" /></td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">SQL Server Database:</td>
                    <td>&nbsp;</td>
                    <td><asp:TextBox ID="textboxSqlDatabase" Width="200" runat="server" /></td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">SQL Server Username:</td>
                    <td>&nbsp;</td>
                    <td><asp:TextBox ID="textboxSqlUsername" Width="200" runat="server" /></td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">SQL Server Password:</td>
                    <td>&nbsp;</td>
                    <td><asp:TextBox ID="textboxSqlPassword" Width="200" runat="server" /></td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td><asp:Button ID="buttonTestSql" Text="Test SQL Connectivity" OnClick="TestSqlSettingsClick" runat="server" /></td>
                </tr>
               
                <tr>
                    <td colspan="3" class="tableConfigurationSpacer">&nbsp;</td>
                </tr>
                

                <tr>
                    <td colspan="3" class="tableConfigHeader">
                        Facade Configuration<br />
                    </td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">Facade Name:</td>
                    <td>&nbsp;</td>
                    <td>
                        <asp:DropDownList ID="dropdownFacadeName" OnSelectedIndexChanged="ChangeFacade"
                            AutoPostBack="true" runat="server" />
                     </td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">Sites File Name:</td>
                    <td>&nbsp;</td>
                    <td><asp:TextBox ID="textboxFacadeSitesFileName" Width="200" runat="server" /></td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">Production:</td>
                    <td>&nbsp;</td>
                    <td>
                        <asp:RadioButtonList ID="radioFacadeIsProduction" runat="server">
                            <asp:ListItem Selected="True" Text="True" Value="true" />
                            <asp:ListItem Text="False" Value="false" />
                        </asp:RadioButtonList>
                    </td>
                </tr>
                <tr>
                    <td class="tableConfigLabel">Version:</td>
                    <td>&nbsp;</td>
                    <td><asp:TextBox ID="textboxFacadeVersion" ToolTip="Read Only" ReadOnly="true" Width="200" runat="server" /></td>
                </tr>
                <tr>
                    <td colspan="3" class="tableConfigurationSpacer">&nbsp;</td>
                </tr>

                <tr>
                    <td class="tableConfigLabel">Have You Seen My Sites File?</td>
                    <td>&nbsp;</td>
                    <td>
                        <asp:Button ID="buttonGetSitesFile" Text="Download Latest Sites File" OnClick="DownloadSitesFile_Click" runat="server" />
                    </td>
                </tr>

                <tr>
                    <td id="versionLabel" colspan="3">Logged in as <asp:Label ID="labelUser" runat="server"></asp:Label></td>
                </tr>
            </tbody>
        </table>
        </div>
        
        <br /><br />
    
        <asp:Button  ID="buttonSubmitChanges" Text="Submit Changes" OnClick="SubmitChangesClick" runat="server" />
        </asp:Panel>
    </div>
    </form>
    </div>
</div></body>
</html>
